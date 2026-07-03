<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'finfoco:backup {--keep=14 : Quantos backups diários manter}';

    protected $description = 'Gera dump gzipado do banco em storage/app/backups e apaga os antigos';

    public function handle(): int
    {
        $db   = config('database.connections.mysql');
        $dir  = storage_path('app/backups');
        $file = $dir . '/finfoco-' . now()->format('Y-m-d_His') . '.sql.gz';

        File::ensureDirectoryExists($dir);

        // Hostinger bloqueia shell_exec/exec — só proc_open é permitido.
        // Por isso: binário localizado por caminho direto e gzip feito em PHP.
        $bin = collect(['/usr/bin/mysqldump', '/usr/bin/mariadb-dump', '/usr/sbin/mysqldump'])
            ->first(fn($p) => is_executable($p));
        if (!$bin) {
            $this->error('mysqldump/mariadb-dump não encontrado.');
            return self::FAILURE;
        }

        $cmd = [$bin, '--single-transaction', '--skip-lock-tables',
                '--user=' . $db['username'], $db['database']];
        if (!empty($db['unix_socket'])) {
            array_splice($cmd, 1, 0, ['--socket=' . $db['unix_socket']]);
        } else {
            array_splice($cmd, 1, 0, ['--host=' . $db['host'], '--port=' . (string) $db['port']]);
        }

        // Senha via env do processo — não aparece na lista de processos
        $process = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes,
                             null, getenv() + ['MYSQL_PWD' => $db['password']]);
        if (!is_resource($process)) {
            $this->error('Não foi possível iniciar o mysqldump.');
            return self::FAILURE;
        }

        $gz = gzopen($file, 'wb6');
        while (!feof($pipes[1])) {
            $chunk = fread($pipes[1], 1024 * 512);
            if ($chunk !== false && $chunk !== '') gzwrite($gz, $chunk);
        }
        gzclose($gz);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit !== 0 || !file_exists($file) || filesize($file) < 100) {
            @unlink($file);
            $this->error("Backup falhou (exit {$exit}): {$stderr}");
            return self::FAILURE;
        }

        $this->info('Backup gerado: ' . $file . ' (' . round(filesize($file) / 1024, 1) . ' KB)');

        // Retenção: mantém só os N mais recentes
        $keep = (int) $this->option('keep');
        collect(File::glob($dir . '/finfoco-*.sql.gz'))
            ->sortDesc()
            ->slice($keep)
            ->each(function ($antigo) {
                File::delete($antigo);
                $this->line('Removido backup antigo: ' . basename($antigo));
            });

        return self::SUCCESS;
    }
}

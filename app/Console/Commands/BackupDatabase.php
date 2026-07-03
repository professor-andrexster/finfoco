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

        // mysqldump (ou mariadb-dump) precisa estar no PATH — presente na Hostinger e no dev
        $bin = trim((string) shell_exec('command -v mysqldump || command -v mariadb-dump'));
        if ($bin === '') {
            $this->error('mysqldump/mariadb-dump não encontrado no PATH.');
            return self::FAILURE;
        }

        // Credenciais via variáveis de ambiente do processo (não aparecem em `ps`)
        $env = [
            'MYSQL_PWD' => $db['password'],
        ];
        $socket = !empty($db['unix_socket']) ? '--socket=' . escapeshellarg($db['unix_socket'])
                                             : '--host=' . escapeshellarg($db['host']) . ' --port=' . escapeshellarg((string) $db['port']);

        $cmd = sprintf(
            '%s %s --user=%s --single-transaction --skip-lock-tables %s | gzip > %s',
            escapeshellcmd($bin),
            $socket,
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($file)
        );

        $process = proc_open($cmd, [2 => ['pipe', 'w']], $pipes, null, $env + getenv());
        $stderr  = stream_get_contents($pipes[2]);
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

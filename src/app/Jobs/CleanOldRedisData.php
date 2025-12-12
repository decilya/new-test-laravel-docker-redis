<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CleanOldRedisData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Время жизни данных в Redis (в минутах).
     * Записи старше этого срока будут удалены.
     *
     * @var int
     */
    public $ttlMinutes = 60;

    /**
     * Количество ключей для обработки за один проход (оптимизация).
     *
     * @var int
     */
    public $batchSize = 100;

    /**
     * Шаблон поиска ключей (можно настроить под свои нужды).
     * Например: 'user:*', 'session:*', '*' (все ключи).
     *
     * @var string
     */
    public $keyPattern = '*';

    /**
     * Execute the job.
     */
    public function handle()
    {
        $cutoff = now()->subMinutes($this->ttlMinutes)->timestamp;
        $deletedCount = 0;
        $cursor = null;

        Log::info('Starting Redis cleanup. Pattern: ' . $this->keyPattern . ', TTL: ' . $this->ttlMinutes . ' minutes');

        do {
            // Сканируем ключи по шаблону (пакетами)
            $scanResult = Redis::scan(
                $cursor,
                ['match' => $this->keyPattern, 'count' => $this->batchSize]
            );

            if (empty($scanResult)) {
                break;
            }

            $cursor = $scanResult[0];
            $keys = $scanResult[1];

            foreach ($keys as $key) {
                // Получаем время истечения (TTL) ключа
                $ttl = Redis::ttl($key);

                // Если TTL отрицательный — ключ уже просрочен
                if ($ttl === -2) {
                    Redis::del($key);
                    $deletedCount++;
                } // Если TTL положительный, но время жизни меньше cutoff — удаляем
                elseif ($ttl > 0 && ($cutoff > (Redis::pttl($key) / 1000 + now()->timestamp))) {
                    Redis::del($key);
                    $deletedCount++;
                }
            }
        } while ($cursor !== '0');

        Log::info("Redis cleanup completed. Deleted {$deletedCount} keys.");
    }
}

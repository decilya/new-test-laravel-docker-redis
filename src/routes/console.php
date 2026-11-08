<?php

use App\Jobs\CleanupOldDataJob;
use Illuminate\Support\Facades\Schedule;

// Из ТЗ: Запуск джобы по расписанию каждые N минут
Schedule::job(new CleanupOldDataJob)->everyFiveMinutes();

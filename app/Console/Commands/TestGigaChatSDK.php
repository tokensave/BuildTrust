<?php

namespace App\Console\Commands;

use App\Services\AI\GigaChatService;
use Illuminate\Console\Command;

class TestGigaChatSDK extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gigachat:test-sdk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тест GigaChat SDK для проверки контрагента';

    /**
     * Execute the console command.
     */
    public function handle(GigaChatService $gigaChatService)
    {
        $this->info('🚀 Тестирование GigaChat SDK...');
        
        try {
            $result = $gigaChatService->checkCounterparty('7736050003', 'ПАО "Сбербанк"');
            
            $this->info('✅ Успех! Ответ получен:');
            $this->line($result['description']);
            $this->info('Дата проверки: ' . $result['check_date']);
            
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

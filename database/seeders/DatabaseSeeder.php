<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🏢 Создаем компании...');
        
        // Создаем компании
        $companies = Company::factory()->count(15)->create();
        
        $this->command->info('👥 Создаем пользователей...');
        
        // Создаем пользователей и привязываем к компаниям
        $users = collect();
        
        // Создаем пользователей без компаний (freelancers)
        $freelancers = User::factory()->count(10)->create([
            'company_id' => null,
        ]);
        $users = $users->merge($freelancers);
        
        // Создаем пользователей с компаниями
        foreach ($companies as $company) {
            // Каждая компания имеет 1-3 пользователей
            $companyUsers = User::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create([
                    'company_id' => $company->id,
                ]);
            $users = $users->merge($companyUsers);
        }
        
        $this->command->info('📢 Создаем объявления...');
        
        // Создаем объявления для пользователей
        $ads = collect();
        foreach ($users as $user) {
            // Каждый пользователь создает от 0 до 5 объявлений
            $userAds = Ad::factory()
                ->count(fake()->numberBetween(0, 5))
                ->create([
                    'user_id' => $user->id,
                ]);
            $ads = $ads->merge($userAds);
        }
        
        $this->command->info('🤝 Создаем сделки...');
        
        // Создаем сделки между пользователями
        $publishedAds = $ads->where('status', 'published');
        $deals = collect();
        
        for ($i = 0; $i < 25; $i++) {
            if ($publishedAds->isEmpty()) break;
            
            $ad = $publishedAds->random();
            $seller = $ad->user;
            
            // Покупатель не должен быть продавцом
            $availableBuyers = $users->where('id', '!=', $seller->id);
            if ($availableBuyers->isEmpty()) continue;
            
            $buyer = $availableBuyers->random();
            
            $deal = Deal::factory()->create([
                'ad_id' => $ad->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
            ]);
            
            $deals->push($deal);
        }
        
        $this->command->info('💬 Создаем треды и сообщения...');
        
        // Создаем треды для некоторых объявлений
        $threadsCount = 0;
        $messagesCount = 0;
        
        foreach ($publishedAds->take(20) as $ad) {
            // 60% шанс создать тред для объявления
            if (fake()->boolean(60)) {
                $thread = Thread::factory()->create([
                    'ad_id' => $ad->id,
                ]);
                $threadsCount++;
                
                // Добавляем участников в тред
                $participants = $users->where('id', '!=', $ad->user_id)
                    ->random(fake()->numberBetween(1, 3));
                
                $allParticipants = collect([$ad->user])->merge($participants)->unique('id');
                
                foreach ($allParticipants as $participant) {
                    $thread->participants()->attach($participant->id, [
                        'joined_at' => fake()->dateTimeBetween('-1 month', 'now'),
                    ]);
                }
                
                // Создаем сообщения в треде
                $messageCount = fake()->numberBetween(2, 10);
                for ($j = 0; $j < $messageCount; $j++) {
                    Message::factory()->create([
                        'thread_id' => $thread->id,
                        'author_id' => $allParticipants->random()->id,
                    ]);
                    $messagesCount++;
                }
            }
        }
        
        $this->command->info('✅ Seeding завершен!');
        $this->command->table(
            ['Модель', 'Количество'],
            [
                ['Компании', $companies->count()],
                ['Пользователи', $users->count()],
                ['Объявления', $ads->count()],
                ['Сделки', $deals->count()],
                ['Треды', $threadsCount],
                ['Сообщения', $messagesCount],
            ]
        );
    }
}

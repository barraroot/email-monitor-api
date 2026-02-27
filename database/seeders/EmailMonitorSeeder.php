<?php

namespace Database\Seeders;

use App\Models\AuthEvent;
use App\Models\CpanelAccount;
use App\Models\IngestClient;
use App\Models\Mailbox;
use App\Models\MailEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmailMonitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory()->count(2)->create();

        $users->each(function (User $user): void {
            CpanelAccount::factory()->for($user)->create();
        });

        MailEvent::factory()->count(300)->create();
        AuthEvent::factory()->count(200)->create();
        Mailbox::factory()->count(20)->create();
        IngestClient::factory()->count(1)->create();
    }
}

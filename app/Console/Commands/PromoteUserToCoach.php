<?php

namespace App\Console\Commands;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToCoach extends Command
{
    protected $signature = 'user:promote-coach {email : The email address of the user to promote}';

    protected $description = 'Promote a user to the Coach role';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");

            return self::FAILURE;
        }

        if ($user->isCoach()) {
            $this->info("{$user->name} ({$email}) is already a Coach.");

            return self::SUCCESS;
        }

        $user->update(['user_type' => UserType::Coach]);

        $this->info("Successfully promoted {$user->name} ({$email}) to Coach.");

        return self::SUCCESS;
    }
}

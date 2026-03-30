<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Argument;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:make-staff {email : Email do utilizador}')]
#[Description('Promove um utilizador a staff')]
class MakeStaff extends Command
{
    public function handle(): void
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Utilizador não encontrado: {$email}");
            return;
        }

        $user->update(['role' => 'staff']);
        $this->info("✓ {$user->name} é agora staff.");
    }
}

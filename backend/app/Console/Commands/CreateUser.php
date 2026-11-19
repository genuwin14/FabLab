<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Creates an admin or staff account from the command line.
 *
 * The app has no screen for this. Admin → Users lists accounts and switches
 * them on or off, and sign-up only ever makes customers. So on a fresh server
 * this command makes the first admin, and any staff after that. The only
 * other source of such accounts is the demo seeder, and every account it
 * makes has the password "password".
 */
class CreateUser extends Command
{
    /**
     * @var string
     */
    protected $signature = 'users:create
                            {--role= : admin or staff}
                            {--name= : Full name}
                            {--email= : The e-mail address they sign in with}
                            {--phone= : Mobile number such as 09171234567 (optional)}
                            {--password= : Leave it out to be asked instead, which keeps it out of the shell history}';

    /**
     * @var string
     */
    protected $description = 'Create an admin or staff account';

    public function handle(): int
    {
        $input = [
            'role' => $this->option('role') ?? select(
                label: 'Role',
                options: ['admin' => 'Admin', 'staff' => 'Staff'],
            ),
            'fullname' => $this->option('name') ?? text(label: 'Full name', required: true),
            'email' => $this->option('email') ?? text(label: 'E-mail address (they sign in with it)', required: true),
            'contact_number' => $this->option('phone') ?? text(
                label: 'Mobile number (optional)',
                placeholder: '09171234567',
                hint: 'Password-reset codes can be texted here.',
            ),
            'password' => $this->option('password') ?? password(
                label: 'Password',
                required: true,
                hint: 'At least 8 characters.',
            ),
        ];

        $validator = Validator::make($input, [
            'role' => ['required', 'in:admin,staff'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            return self::FAILURE;
        }

        $phone = trim((string) $input['contact_number']);

        $user = User::create([
            'role' => $input['role'],
            'fullname' => $input['fullname'],
            'email' => $input['email'],
            'contact_number' => $phone !== '' ? $phone : null,
            'password' => $input['password'],
            // Whoever ran this command vouches for the account, so it skips
            // the sign-up code that customers verify with.
            'email_verified_at' => now(),
            'phone_verified' => $phone !== '',
            'status' => 'active',
        ]);

        $this->components->info(sprintf(
            'Created the %s account %s. Sign in at %s',
            $user->role,
            $user->email,
            url('/login'),
        ));

        return self::SUCCESS;
    }
}

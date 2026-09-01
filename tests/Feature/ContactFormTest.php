<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Awa Traoré',
        'email' => 'Awa@Example.com',
        'subject' => 'Demande de partenariat',
        'message' => 'Bonjour, je souhaite proposer un partenariat à votre association.',
    ];

    /**
     * Régression : le formulaire annonçait « message envoyé » alors que le
     * contrôleur ne faisait qu'un Log::info — aucun message ne parvenait à l'équipe.
     */
    public function test_message_is_emailed_to_administrators(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@fsl.test']);
        User::factory()->create(['is_admin' => false, 'email' => 'membre@fsl.test']);

        $this->post(route('contact.send'), $this->payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        Mail::assertQueued(ContactMail::class, 1);
        Mail::assertQueued(ContactMail::class, fn (ContactMail $mail) => $mail->hasTo($admin->email)
            && $mail->data['subject'] === 'Demande de partenariat'
            && $mail->data['email'] === 'awa@example.com');
    }

    public function test_message_falls_back_to_configured_from_address_when_no_admin_exists(): void
    {
        Mail::fake();
        config(['mail.from.address' => 'contact@fsl.test']);

        $this->post(route('contact.send'), $this->payload)->assertRedirect();

        Mail::assertQueued(ContactMail::class, fn (ContactMail $mail) => $mail->hasTo('contact@fsl.test'));
    }

    public function test_validation_errors_reopen_the_contact_modal(): void
    {
        Mail::fake();

        $this->post(route('contact.send'), ['name' => '', 'email' => 'pas-un-email'])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message'])
            ->assertSessionHas('modal', 'contact');

        Mail::assertNothingQueued();
    }

    public function test_honeypot_blocks_bots_without_sending_email(): void
    {
        Mail::fake();
        User::factory()->create(['is_admin' => true]);

        $this->post(route('contact.send'), $this->payload + ['website' => 'http://spam.test'])
            ->assertRedirect();

        Mail::assertNothingQueued();
    }
}

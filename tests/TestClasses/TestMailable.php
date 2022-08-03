<?php

namespace Creagia\LaravelWebMailer\Tests\TestClasses;

use Illuminate\Mail\Mailable;

class TestMailable extends Mailable
{
    public function build()
    {
        $this
            ->to(['john@example.com', 'john1@example.com'])
            ->cc(['paul@example.com', 'paul1@example.com'])
            ->bcc('george@example.com')
            ->replyTo('support@example.com')
            ->from('ringo@example.com', 'Ringo')
            ->subject('this is the subject')
            ->html('<h1>title</h1><p>this is the html</p>')
            ->attachData('lorem ipsum', 'dummy.text', ['mime' => 'text/plain']);
    }
}

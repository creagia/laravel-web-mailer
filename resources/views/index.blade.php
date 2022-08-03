<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Web Inbox</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        const mailClient = () => ({
            tab: 'html',
            mails: [],
            currentMail: false,
            timer: 5000,
            init() {
                this.retrieveMails();
                this.initPolling();
                this.$watch('currentMail.htmlBody', (htmlContent) => {
                    this.fillHtmlTabIframe(htmlContent)
                });
            },
            initPolling() {
                setInterval(() => this.retrieveMails(), this.timer);
            },
            fillHtmlTabIframe(htmlContent) {
                const iframe = document.getElementById('htmlContentTab');
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write(htmlContent);
                iframe.contentWindow.document.close();
            },
            showMail(index) {
                this.mails[index].isRead = true;
                let fetchRoute = this.mails[index].fetchRoute;
                this.retrieveMail(fetchRoute);
            },
            retrieveMail(fetchRoute) {
                fetch(fetchRoute)
                    .then((response) => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error('Something went wrong. ' + response.statusText);
                    })
                    .then((json) => {
                        this.currentMail = json
                        this.fillHtmlTabIframe();
                    })
                    .catch(error => {
                        alert(error);
                        this.currentMail = false;
                    });
            },
            async retrieveMails() {
                this.mails = await (await fetch("{{ route('laravelWebMailer.fetchAll') }}")).json();
            },
            async confirmAndDeleteAllMails() {
                if (!confirm('Are you sure you want to delete all emails?')) {
                    return;
                }

                fetch("{{ route('laravelWebMailer.destroy') }}", {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name=csrf-token]').content
                    }
                })
                    .then((response) => {
                        if (response.ok) {
                            return response.json();
                        }
                        if (response.status === 419) {
                            throw new Error('Validation Token was expired. Please reload the page and try again');
                        }
                        throw new Error('Something went wrong. ' + response.statusText);
                    })
                    .then(() => {
                        this.currentMail = false;
                        this.mails = [];
                    })
                    .catch(error => {
                        alert(error);
                        this.currentMail = false;
                    });
            },
        });
    </script>
</head>
<body>
<div class="flex flex-col h-screen"
     x-data="mailClient"
>

    <div class="bg-neutral-200 px-2 py-3 flex justify-between items-center">
        <h1 class="font-bold text-xl">{{ config('app.name') }} — Web Inbox</h1>
        <div>
            <button @click="confirmAndDeleteAllMails()"
                    class="flex items-center text-sm rounded-full border-2 border-neutral-700 px-3 py-1 hover:border-red-400 hover:text-red-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3"
                     viewBox="0 0 16 16">
                    <path
                        d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
                </svg>
                <span class="ml-1">Delete all messages</span>
            </button>
        </div>
    </div>

    <div class="flex flex-grow overflow-x-hidden">

        <main x-cloack x-show="! mails.length" class="w-full h-full bg-neutral-100 flex items-center justify-center">
            <div class="border-4 border-dotted border-gray-200 bg-white/50 rounded-xl p-5 text-center">
                <div class="text-lg">There are no emails</div>
                @if(config('mail.default') !== 'web')
                    <div>
                        <a href="https://github.com/creagia/laravel-web-mailer#installation" class="underline" target="_blank">Set the  `web` mailer in your .env file.</a>
                    </div>
                @endif
            </div>
        </main>

        <aside x-show="mails.length" class="h-full overflow-y-scroll flex-none w-[300px] overflow-x-hidden">
            <template
                x-show="mails.length"
                x-for="(mail, index) in mails"
                :key="mail.messageId"
            >
                <div class="mail-item p-2 border-b border-neutral-200 cursor-pointer border-l-4"
                     @click="showMail(index)"
                     :class="{'bg-neutral-100' : mail.isRead, 'border-x-blue-500' : (mail.messageId===currentMail.messageId), 'border-x-transparent' : (mail.messageId!==currentMail.messageId) }"
                >
                    <div class="flex">
                        <div class="text-sm leading-tight" :class="{'font-bold' : !mail.isRead}"
                             x-text="mail.subject"></div>
                        <div x-show="mail.hasAttachments">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-paperclip" viewBox="0 0 16 16">
                                <path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0V3z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="flex gap-1 items-center">
                        <div class="truncate flex-grow">
                            <template x-for="emailAddress in mail.to">
                                <span class="text-xs" x-text="'<'+emailAddress+'> '"></span>
                            </template>
                        </div>
                        <div class="text-xs whitespace-nowrap text-neutral-400" x-text="mail.diffDate"></div>
                    </div>
                </div>
            </template>
        </aside>
        <main x-show="currentMail" class="h-full flex-grow flex flex-col overflow-hidden">
            <div class="bg-neutral-100 px-4 py-2">
                <div class="font-bold text-lg" x-text="currentMail.subject"></div>
                <div class="text-sm text-neutral-700">Sent at: <span x-text="currentMail.sentAtFormatted"></span></div>

                <table class="table-fixed my-2 bg-white border w-full text-sm">
                    <tbody>
                    <tr class="border-b border-neutral-200">
                        <td class="px-4 py-2 w-[200px]">From</td>
                        <td class="px-4 py-2"><span x-text="currentMail.from"></span></td>
                    </tr>
                    <tr class="border-b border-neutral-200">
                        <td class="px-4 py-2">To</td>
                        <td class="px-4 py-2"><span x-text="currentMail.to"></span></td>
                    </tr>
                    <tr class="border-b border-neutral-200" x-show="currentMail && currentMail.cc.length>0">
                        <td class="px-4 py-2">CC</td>
                        <td class="px-4 py-2"><span x-text="currentMail.cc"></span></td>
                    </tr>
                    <tr class="border-b border-neutral-200" x-show="currentMail && currentMail.bcc.length>0">
                        <td class="px-4 py-2">BCC</td>
                        <td class="px-4 py-2"><span x-text="currentMail.bcc"></span></td>
                    </tr>
                    <tr class="border-b border-neutral-200" x-show="currentMail && currentMail.replyTo.length>0">
                        <td class="px-4 py-2">Reply-To</td>
                        <td class="px-4 py-2"><span x-text="currentMail.replyTo"></span></td>
                    </tr>
                    <tr class="border-b border-neutral-200" x-show="currentMail && currentMail.attachments.length>0">
                        <td class="px-4 py-2">Attachments</td>
                        <td class="px-4 py-2">
                            <template x-for="attachment in currentMail.attachments">
                                <div>
                                    <a :href="attachment.downloadRoute" target="_blank"
                                       class="inline-flex items-center py-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16">
                                            <path
                                                d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                                        </svg>
                                        <span x-text="attachment.filename" class="ml-1"></span>
                                    </a>
                                </div>
                            </template>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <nav class="py-2 space-x-1">
                    <button
                        class="font-semibold px-3 py-2 text rounded-lg hover:bg-white hover:text-neutral-900"
                        :class="{ 'bg-white text-neutral-900': tab === 'html', 'bg-neutral-200 text-neutral-700': tab !== 'html'  }"
                        @click.prevent="tab = 'html';">HTML
                    </button>
                    <button
                        class="font-semibold px-3 py-2 text rounded-lg hover:bg-white hover:text-neutral-900"
                        :class="{ 'bg-white text-neutral-900': tab === 'html-source','bg-neutral-200 text-neutral-700': tab !== 'html-source' } "
                        @click.prevent="tab = 'html-source';">HTML-Source
                    </button>
                    <button
                        class="font-semibold px-3 py-2 text rounded-lg hover:bg-white hover:text-neutral-900"
                        :class="{ 'bg-white text-neutral-900': tab === 'text','bg-neutral-200 text-neutral-700': tab !== 'text' } "
                        @click.prevent="tab = 'text';">Text
                    </button>
                    <button
                        class="font-semibold px-3 py-2 text rounded-lg hover:bg-white hover:text-neutral-900"
                        :class="{ 'bg-white text-neutral-900': tab === 'raw','bg-neutral-200 text-neutral-700': tab !== 'raw' } "
                        @click.prevent="tab = 'raw';">Raw
                    </button>
                    <button
                        class="font-semibold px-3 py-2 text rounded-lg hover:bg-white hover:text-neutral-900"
                        :class="{ 'bg-white text-neutral-900': tab === 'headers','bg-neutral-200 text-neutral-700': tab !== 'headers' } "
                        @click.prevent="tab = 'headers';">
                        Headers
                    </button>
                </nav>
            </div>

            <div class="p-4 flex-grow overflow-auto w-full">
                <div x-show="tab === 'html'" class="h-full">
                    <iframe id="htmlContentTab" class="h-full w-full"></iframe>
                </div>
                <div x-show="tab === 'html-source'" class="text-sm">
                    <pre x-text="currentMail.htmlBody"></pre>
                </div>
                <div x-show="tab === 'text'" class="text-sm">
                    <div x-html="currentMail.textBody"></div>
                </div>
                <div x-show="tab === 'raw'" class="text-sm">
                    <pre x-text="currentMail.eml"></pre>
                </div>
                <div x-show="tab === 'headers'" class="text-sm">
                    <pre x-html="currentMail.headers"></pre>
                </div>
            </div>
        </main>

    </div>
</div>

</body>
</html>

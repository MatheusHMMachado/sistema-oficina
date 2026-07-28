{{-- Copiloto IA — widget flutuante --}}
<div x-data="copilotoWidget()"
     x-cloak
     class="fixed bottom-6 right-6 z-[90] flex flex-col items-end"
     style="font-family: inherit;">

    {{-- Painel de chat --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-4 w-[min(100vw-2rem,400px)] h-[min(70vh,560px)] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden">

        <div class="px-4 py-3 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <p class="font-bold text-sm leading-tight">Copiloto IA</p>
                    <p class="text-xs text-white/85" x-text="modeLabel"></p>
                </div>
            </div>
            <div class="flex items-center space-x-1">
                <button type="button" @click="clearChat()" class="p-2 rounded-lg hover:bg-white/15" title="Limpar conversa">
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>
                <button type="button" @click="open = false" class="p-2 rounded-lg hover:bg-white/15" title="Fechar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="px-3 py-2 border-b flex flex-wrap gap-2"
             style="background: color-mix(in srgb, var(--brand-primary) 10%, white); border-color: color-mix(in srgb, var(--brand-primary) 25%, white);">
            <template x-for="chip in suggestions" :key="chip">
                <button type="button"
                        @click="sendQuick(chip)"
                        class="text-[11px] px-2.5 py-1 rounded-full bg-white border transition-colors hover:opacity-90"
                        style="border-color: color-mix(in srgb, var(--brand-primary) 35%, white); color: var(--brand-primary);"
                        x-text="chip"></button>
            </template>
        </div>

        <div x-ref="messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gradient-to-b from-gray-50 to-white">
            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                        ? 'max-w-[85%] rounded-2xl rounded-br-md text-white px-3.5 py-2.5 text-sm shadow'
                        : 'max-w-[90%] rounded-2xl rounded-bl-md bg-white border border-gray-200 text-gray-800 px-3.5 py-2.5 text-sm shadow-sm'"
                         :style="msg.role === 'user' ? 'background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary))' : ''">
                        <p class="whitespace-pre-wrap break-words" x-text="msg.content"></p>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-3 text-sm text-gray-500 shadow-sm">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Pensando...
                </div>
            </div>
        </div>

        <form @submit.prevent="send()" class="p-3 border-t border-gray-200 bg-white">
            <div class="flex items-end gap-2">
                <button type="button"
                        @click="toggleVoice()"
                        :class="listening ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shrink-0"
                        :title="listening ? 'Parar áudio' : 'Falar'">
                    <i class="fas" :class="listening ? 'fa-stop' : 'fa-microphone'"></i>
                </button>
                <textarea x-model="input"
                          @keydown.enter.prevent="if (!$event.shiftKey) send()"
                          rows="1"
                          placeholder="Pergunte ou peça uma ação..."
                          class="flex-1 resize-none px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-primary focus:border-transparent max-h-28"></textarea>
                <button type="submit"
                        :disabled="loading || !input.trim()"
                        class="w-10 h-10 rounded-xl btn-primary text-white flex items-center justify-center shrink-0 disabled:opacity-50">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <p class="text-[10px] text-gray-400 mt-1.5 px-1">Enter envia · Shift+Enter nova linha · Microfone opcional</p>
        </form>
    </div>

    {{-- Botão flutuante --}}
    <button type="button"
            @click="open = !open; if (open) $nextTick(() => scrollBottom())"
            class="w-14 h-14 rounded-full shadow-2xl text-white flex items-center justify-center transition-transform hover:scale-105 focus:outline-none"
            style="background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%); box-shadow: 0 10px 25px rgba(var(--brand-primary-rgb), 0.45);"
            :title="open ? 'Fechar Copiloto' : 'Abrir Copiloto IA'">
        <i class="fas text-xl" :class="open ? 'fa-times' : 'fa-robot'"></i>
    </button>
</div>

<script>
function copilotoWidget() {
    return {
        open: false,
        loading: false,
        listening: false,
        input: '',
        mode: 'local',
        recognition: null,
        messages: [
            {
                role: 'assistant',
                content: 'Olá! Sou o Copiloto do Sistema Oficina.\n\nPosso ensinar (tutoriais), tirar dúvidas e cadastrar por você: clientes, veículos, peças/serviços, OS e despesas.\n\nTente: \"lista de tutoriais\" ou \"como cadastrar cliente\".'
            }
        ],
        suggestions: [
            'Lista de tutoriais',
            'Tutorial clientes',
            'Tutorial peças',
            'Dúvidas frequentes',
            'Resumo da oficina'
        ],

        get modeLabel() {
            if (this.mode === 'llm') return 'IA conectada';
            if (this.mode === 'local_fallback') return 'Modo local (fallback)';
            if (this.mode === 'disabled') return 'Desativado';
            return 'Modo local';
        },

        init() {
            this.fetchStatus();
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SpeechRecognition) {
                this.recognition = new SpeechRecognition();
                this.recognition.lang = 'pt-BR';
                this.recognition.interimResults = false;
                this.recognition.onresult = (event) => {
                    const text = event.results[0][0].transcript;
                    this.input = (this.input ? this.input + ' ' : '') + text;
                    this.listening = false;
                };
                this.recognition.onerror = () => { this.listening = false; };
                this.recognition.onend = () => { this.listening = false; };
            }
        },

        async fetchStatus() {
            try {
                const res = await fetch('{{ route('copiloto.status') }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.mode = data.mode || 'local';
                }
            } catch (e) {}
        },

        clearChat() {
            this.messages = [{
                role: 'assistant',
                content: 'Conversa limpa. Em que posso ajudar?'
            }];
        },

        sendQuick(text) {
            this.input = text;
            this.send();
        },

        scrollBottom() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },

        toggleVoice() {
            if (!this.recognition) {
                alert('Seu navegador não suporta ditado por voz. Use Chrome ou Edge.');
                return;
            }
            if (this.listening) {
                this.recognition.stop();
                this.listening = false;
                return;
            }
            this.listening = true;
            this.recognition.start();
        },

        async send() {
            const text = (this.input || '').trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', content: text });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.scrollBottom());

            const history = this.messages
                .filter(m => m.role === 'user' || m.role === 'assistant')
                .slice(0, -1)
                .slice(-12)
                .map(m => ({ role: m.role, content: m.content }));

            try {
                const res = await fetch('{{ route('copiloto.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message: text, history })
                });

                const data = await res.json();
                if (!res.ok) {
                    const err = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Erro ao falar com o Copiloto.');
                    this.messages.push({ role: 'assistant', content: '❌ ' + err });
                } else {
                    this.mode = data.mode || this.mode;
                    this.messages.push({ role: 'assistant', content: data.reply || 'Sem resposta.' });
                }
            } catch (e) {
                this.messages.push({ role: 'assistant', content: '❌ Falha de conexão com o servidor.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollBottom());
            }
        }
    }
}
</script>

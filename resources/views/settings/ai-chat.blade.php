@extends('layouts.app')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Settings</p>
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-50">AI Assistant (Admin)</h1>
            <p class="text-xs text-slate-400 mt-1">Ask questions about your shop and get insights via an AI API.</p>
        </div>
        <div class="hidden md:flex items-center space-x-2 text-[11px] text-slate-400">
            <span>Settings</span>
            <span class="text-slate-500">/</span>
            <span class="text-cyan-300">AI Assistant</span>
        </div>
    </div>
@endsection

@section('content')
    <div
        class="glass-panel px-4 py-4 md:px-6 md:py-6 space-y-4"
        x-data="{
            apiKey: '',
            model: 'gpt-4.1-mini',
            question: '',
            isSending: false,
            messages: [],
            async send() {
                if (!this.question.trim() || !this.apiKey.trim()) {
                    return;
                }

                const content = this.question.trim();
                this.messages.push({ role: 'user', content });

                this.question = '';
                this.isSending = true;

                try {
                    const response = await fetch('https://api.openai.com/v1/chat/completions', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + this.apiKey.trim(),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            model: this.model,
                            messages: this.messages,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('API error');
                    }

                    const data = await response.json();
                    const reply = data.choices?.[0]?.message?.content ?? '';

                    if (reply) {
                        this.messages.push({ role: 'assistant', content: reply });
                    }
                } catch (error) {
                    console.error(error);
                    this.messages.push({
                        role: 'assistant',
                        content: 'There was a problem contacting the AI API. Please check your key and internet connection.',
                    });
                } finally {
                    this.isSending = false;
                }
            }
        }"
    >
        <div class="space-y-3">
            <div class="space-y-1.5">
                <label class="text-[11px] text-slate-300">API Key</label>
                <input
                    type="password"
                    x-model="apiKey"
                    placeholder="Paste your AI provider API key here"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                >
                <p class="text-[11px] text-slate-500">
                    This key is stored in your browser memory only for this session. For production, move this to a secure backend configuration.
                </p>
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] text-slate-300">Question</label>
                <textarea
                    rows="3"
                    x-model="question"
                    placeholder="Ask about sales trends, popular products, or debt levels..."
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                ></textarea>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-[11px] text-slate-500">
                    Example: “Which products sold the most this week and what does that suggest about my stock levels?”
                </p>
                <button
                    type="button"
                    @click="send"
                    :disabled="!question.trim() || !apiKey.trim() || isSending"
                    class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-4 py-2 text-[11px] font-semibold tracking-tight text-slate-950 shadow-lg shadow-cyan-500/40 transition-transform duration-150 hover:bg-cyan-400 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span x-show="!isSending">Ask AI</span>
                    <span x-show="isSending" class="flex items-center space-x-2">
                        <svg class="h-4 w-4 animate-spin text-slate-900" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 0v4a8 8 0 00-8 8h4z"></path>
                        </svg>
                        <span>Thinking...</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="border-t border-white/10 pt-4">
            <h2 class="text-sm font-semibold tracking-tight text-slate-50 mb-2">Conversation</h2>
            <div class="max-h-[360px] overflow-y-auto space-y-2 pr-1">
                <template x-for="(message, index) in messages" :key="index">
                    <div
                        class="rounded-xl px-3 py-2 text-[11px]"
                        :class="message.role === 'user'
                            ? 'bg-cyan-500/10 text-cyan-100 border border-cyan-500/30'
                            : 'bg-white/5 text-slate-100 border border-white/10'"
                    >
                        <p class="text-[10px] uppercase tracking-[0.18em] mb-1"
                           :class="message.role === 'user' ? 'text-cyan-300' : 'text-emerald-300'">
                            <span x-text="message.role === 'user' ? 'You' : 'AI Assistant'"></span>
                        </p>
                        <p x-text="message.content"></p>
                    </div>
                </template>

                <p x-show="messages.length === 0" class="text-[11px] text-slate-500">
                    No messages yet. Ask a question to start a conversation.
                </p>
            </div>
        </div>
    </div>
@endsection
/**
 * ==============================================================================
 * PROJETO: Cozinha Guiada
 * ARQUIVO: public/js/guided-mode.js
 * DESCRIÇÃO: Controlador interativo do Modo Guiado, Wake Lock e Temporizador
 * ATENDE AOS REQUISITOS: RF18, RF19, CA18, CA19
 * ==============================================================================
 */

class GuidedMode {
    constructor(config) {
        this.recipeId = config.recipeId;
        this.recipeTitle = config.recipeTitle;
        this.steps = config.steps || [];
        this.currentIndex = 0;
        this.wakeLockSentinel = null;
        this.wakeLockWanted = true;

        // Temporizador
        this.timerSeconds = 0;
        this.timerInterval = null;
        this.timerRunning = false;

        // Elementos de DOM
        this.dom = {
            progressBar: document.getElementById('progress-bar'),
            stepCounter: document.getElementById('step-counter'),
            stepTotal: document.getElementById('step-total'),
            stepNumberBadge: document.getElementById('step-number-badge'),
            stepDescription: document.getElementById('step-description'),
            btnPrev: document.getElementById('btn-prev'),
            btnNext: document.getElementById('btn-next'),
            wakeLockStatus: document.getElementById('wakelock-status'),
            wakeLockToggle: document.getElementById('wakelock-toggle'),
            timerDisplay: document.getElementById('timer-display'),
            btnTimerStart: document.getElementById('btn-timer-start'),
            btnTimerReset: document.getElementById('btn-timer-reset'),
            completionModal: document.getElementById('completion-modal')
        };

        this.init();
    }

    init() {
        if (!this.steps || this.steps.length === 0) {
            console.error('[Modo Guiado] Nenhuma etapa disponível.');
            return;
        }

        this.bindEvents();
        this.updateStepUI();
        this.initWakeLock();
    }

    bindEvents() {
        // Botões de navegação
        this.dom.btnNext.addEventListener('click', () => this.nextStep());
        this.dom.btnPrev.addEventListener('click', () => this.prevStep());

        // Navegação por teclado (Acessibilidade e praticidade na bancada)
        window.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            if (e.key === 'ArrowRight' || e.key === ' ') {
                e.preventDefault();
                this.nextStep();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.prevStep();
            }
        });

        // Alternância manual de Wake Lock
        if (this.dom.wakeLockToggle) {
            this.dom.wakeLockToggle.addEventListener('click', () => {
                if (this.wakeLockSentinel) {
                    this.wakeLockWanted = false;
                    this.releaseWakeLock();
                } else {
                    this.wakeLockWanted = true;
                    this.requestWakeLock();
                }
            });
        }

        // Reaquisição do Wake Lock ao restaurar foco/visibilidade da aba (RF19)
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && this.wakeLockWanted) {
                this.requestWakeLock();
            }
        });

        // Libera Wake Lock ao sair da página
        window.addEventListener('beforeunload', () => {
            this.releaseWakeLock();
        });

        // Eventos do Temporizador
        if (this.dom.btnTimerStart) {
            this.dom.btnTimerStart.addEventListener('click', () => this.toggleTimer());
        }
        if (this.dom.btnTimerReset) {
            this.dom.btnTimerReset.addEventListener('click', () => this.resetTimer());
        }

        // Presets de tempo (+1m, +3m, +5m, +10m)
        document.querySelectorAll('[data-timer-add]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                const addSeconds = parseInt(e.currentTarget.getAttribute('data-timer-add'), 10) * 60;
                this.addTimerSeconds(addSeconds);
            });
        });
    }

    // --------------------------------------------------------------------------
    // NAVEGAÇÃO DE PASSOS (RF18 / CA18)
    // --------------------------------------------------------------------------

    nextStep() {
        if (this.currentIndex < this.steps.length - 1) {
            this.currentIndex++;
            this.updateStepUI();
        } else {
            // Última etapa concluída -> abre modal de sucesso
            this.showCompletion();
        }
    }

    prevStep() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.updateStepUI();
        }
    }

    goToStep(index) {
        if (index >= 0 && index < this.steps.length) {
            this.currentIndex = index;
            this.updateStepUI();
        }
    }

    updateStepUI() {
        const step = this.steps[this.currentIndex];
        const total = this.steps.length;
        const currentNumber = this.currentIndex + 1;

        // Atualiza contadores e barra de progresso
        this.dom.stepCounter.textContent = currentNumber;
        this.dom.stepTotal.textContent = total;
        this.dom.stepNumberBadge.textContent = `Passo ${currentNumber}`;
        
        const percent = Math.round((currentNumber / total) * 100);
        this.dom.progressBar.style.width = `${percent}%`;

        // Atualiza descrição com animação suave de transição
        this.dom.stepDescription.style.opacity = '0';
        setTimeout(() => {
            this.dom.stepDescription.textContent = step.descricao;
            this.dom.stepDescription.style.opacity = '1';
        }, 150);

        // Atualiza botão Anterior
        if (this.currentIndex === 0) {
            this.dom.btnPrev.setAttribute('disabled', 'true');
            this.dom.btnPrev.classList.add('opacity-40', 'cursor-not-allowed');
        } else {
            this.dom.btnPrev.removeAttribute('disabled');
            this.dom.btnPrev.classList.remove('opacity-40', 'cursor-not-allowed');
        }

        // Atualiza botão Próximo
        if (this.currentIndex === total - 1) {
            this.dom.btnNext.innerHTML = `<span>Finalizar Receita 🎉</span>`;
            this.dom.btnNext.classList.remove('bg-amber-600', 'hover:bg-amber-700');
            this.dom.btnNext.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        } else {
            this.dom.btnNext.innerHTML = `<span>Próximo Passo</span> <span class="text-xl">&rarr;</span>`;
            this.dom.btnNext.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            this.dom.btnNext.classList.add('bg-amber-600', 'hover:bg-amber-700');
        }

        // Rola suavemente para o topo se necessário em dispositivos móveis
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    showCompletion() {
        if (this.dom.completionModal) {
            this.dom.completionModal.classList.remove('hidden');
            this.dom.completionModal.classList.add('flex');
            this.playCelebrationSound();
        }
    }

    // --------------------------------------------------------------------------
    // SCREEN WAKE LOCK API (RF19 / CA19)
    // --------------------------------------------------------------------------

    async initWakeLock() {
        if (!('wakeLock' in navigator)) {
            console.warn('[Wake Lock] API não suportada neste navegador.');
            this.updateWakeLockUI(false, 'Não suportado no navegador atual');
            return;
        }

        if (this.wakeLockWanted) {
            await this.requestWakeLock();
        }
    }

    async requestWakeLock() {
        if (!('wakeLock' in navigator)) return;

        try {
            this.wakeLockSentinel = await navigator.wakeLock.request('screen');
            
            this.wakeLockSentinel.addEventListener('release', () => {
                if (this.wakeLockWanted && document.visibilityState === 'visible') {
                    // Reativa se foi liberado involuntariamente
                    this.requestWakeLock();
                } else {
                    this.updateWakeLockUI(false, 'Bloqueio de tela normal');
                }
            });

            this.updateWakeLockUI(true, 'Tela mantida acesa');
        } catch (err) {
            console.warn(`[Wake Lock] Falha ao ativar: ${err.name}, ${err.message}`);
            this.updateWakeLockUI(false, 'Desativada (economia de bateria)');
        }
    }

    async releaseWakeLock() {
        if (this.wakeLockSentinel) {
            try {
                await this.wakeLockSentinel.release();
            } catch (err) {
                console.error('[Wake Lock] Erro ao liberar:', err);
            } finally {
                this.wakeLockSentinel = null;
                this.updateWakeLockUI(false, 'Bloqueio de tela normal');
            }
        }
    }

    updateWakeLockUI(isActive, label) {
        if (!this.dom.wakeLockStatus) return;

        if (isActive) {
            this.dom.wakeLockStatus.innerHTML = `
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-bold text-emerald-800">${label}</span>
            `;
            this.dom.wakeLockStatus.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-xs shadow-sm transition-all';
        } else {
            this.dom.wakeLockStatus.innerHTML = `
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-stone-400"></span>
                <span class="text-stone-600 font-medium">${label}</span>
            `;
            this.dom.wakeLockStatus.className = 'flex items-center gap-2 px-3 py-1.5 rounded-full bg-stone-100 border border-stone-200 text-xs shadow-sm transition-all';
        }
    }

    // --------------------------------------------------------------------------
    // TEMPORIZADOR INTEGRADO (TIMER CULINÁRIO)
    // --------------------------------------------------------------------------

    toggleTimer() {
        if (this.timerRunning) {
            this.pauseTimer();
        } else {
            this.startTimer();
        }
    }

    startTimer() {
        if (this.timerSeconds <= 0) return;

        this.timerRunning = true;
        this.dom.btnTimerStart.textContent = 'Pausar';
        this.dom.btnTimerStart.className = 'px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-all';

        this.timerInterval = setInterval(() => {
            if (this.timerSeconds > 0) {
                this.timerSeconds--;
                this.renderTimerDisplay();
            } else {
                this.timerFinished();
            }
        }, 1000);
    }

    pauseTimer() {
        this.timerRunning = false;
        clearInterval(this.timerInterval);
        this.dom.btnTimerStart.textContent = 'Retomar';
        this.dom.btnTimerStart.className = 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all';
    }

    resetTimer() {
        this.pauseTimer();
        this.timerSeconds = 0;
        this.renderTimerDisplay();
        this.dom.btnTimerStart.textContent = 'Iniciar';
        this.dom.btnTimerStart.className = 'px-4 py-2 bg-stone-800 hover:bg-stone-900 text-white font-bold text-xs rounded-xl shadow transition-all';
    }

    addTimerSeconds(sec) {
        this.timerSeconds += sec;
        this.renderTimerDisplay();
        if (!this.timerRunning) {
            this.startTimer();
        }
    }

    renderTimerDisplay() {
        const m = Math.floor(this.timerSeconds / 60);
        const s = this.timerSeconds % 60;
        this.dom.timerDisplay.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    timerFinished() {
        this.resetTimer();
        this.playBeepSound();
        alert('⏰ Tempo finalizado! Verifique o ponto da receita.');
    }

    // --------------------------------------------------------------------------
    // EFEITOS SONOROS (WEB AUDIO API NATIVA - SEM ARQUIVOS EXTERNOS)
    // --------------------------------------------------------------------------

    playBeepSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // Nota Lá (A5)
            gain.gain.setValueAtTime(0.3, ctx.currentTime);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start();
            osc.stop(ctx.currentTime + 0.6);
        } catch (e) {
            console.warn('[Audio] Não foi possível reproduzir som:', e);
        }
    }

    playCelebrationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const notes = [523.25, 659.25, 783.99, 1046.50]; // Dó, Mi, Sol, Dó alto
            notes.forEach((freq, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.12);
                gain.gain.setValueAtTime(0.2, ctx.currentTime + i * 0.12);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.12 + 0.25);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(ctx.currentTime + i * 0.12);
                osc.stop(ctx.currentTime + i * 0.12 + 0.25);
            });
        } catch (e) {
            // Silencioso se bloqueado por autoplay policy
        }
    }
}

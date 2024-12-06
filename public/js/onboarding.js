class OnboardingFlow {
    constructor() {
        this.currentStep = parseInt(new URLSearchParams(window.location.search).get('step')) || 1;
        this.userData = {
            language: null,
            level: null,
            dailyGoal: null,
            preferences: {}
        };
        
        this.initializeListeners();
        this.loadSavedProgress();
    }

    initializeListeners() {
        // Language selection
        document.querySelectorAll('.language-card').forEach(card => {
            card.addEventListener('click', (e) => {
                this.handleLanguageSelection(e.currentTarget);
            });
        });

        // Level selection
        document.querySelectorAll('.level-card').forEach(card => {
            card.addEventListener('click', (e) => {
                this.handleLevelSelection(e.currentTarget);
            });
        });

        // Navigation buttons
        document.querySelector('.btn-primary')?.addEventListener('click', () => this.nextStep());
        document.querySelector('.btn-secondary')?.addEventListener('click', () => this.prevStep());
    }

    async handleLanguageSelection(card) {
        const language = card.dataset.language;
        
        // Remove previous selection
        document.querySelectorAll('.language-card').forEach(c => c.classList.remove('selected'));
        
        // Add selection and animation
        card.classList.add('selected', 'pulse');
        setTimeout(() => card.classList.remove('pulse'), 500);

        this.userData.language = language;
        await this.saveProgress();
    }

    async handleLevelSelection(card) {
        const level = card.dataset.level;
        
        document.querySelectorAll('.level-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected', 'pulse');
        setTimeout(() => card.classList.remove('pulse'), 500);

        this.userData.level = level;
        await this.saveProgress();
    }

    async saveProgress() {
        try {
            const response = await fetch('/ajax/save-onboarding-progress.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.userData)
            });
            
            if (!response.ok) throw new Error('Failed to save progress');
            
            const data = await response.json();
            if (data.success) {
                this.showSaveAnimation();
            }
        } catch (error) {
            console.error('Error saving progress:', error);
        }
    }

    showSaveAnimation() {
        const saveIndicator = document.createElement('div');
        saveIndicator.className = 'save-indicator';
        saveIndicator.textContent = '✓ Saved';
        document.body.appendChild(saveIndicator);
        
        setTimeout(() => saveIndicator.remove(), 2000);
    }

    nextStep() {
        this.animateTransition(() => {
            window.location.href = `/view/onboarding.php?step=${this.currentStep + 1}`;
        });
    }

    prevStep() {
        this.animateTransition(() => {
            window.location.href = `/view/onboarding.php?step=${this.currentStep - 1}`;
        });
    }

    animateTransition(callback) {
        const container = document.querySelector('.onboarding-container');
        container.style.opacity = '0';
        container.style.transform = 'translateY(20px)';
        
        setTimeout(callback, 300);
    }
}

// Initialize
const onboarding = new OnboardingFlow(); 
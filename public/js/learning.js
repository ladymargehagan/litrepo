class LearningInterface {
    constructor() {
        this.container = document.querySelector('.exercise-container');
        this.feedback = document.querySelector('.feedback');
        this.celebration = document.querySelector('.celebration');
        this.progressFill = document.querySelector('.progress-fill');
        this.hearts = document.querySelector('.hearts');
        this.currentExercise = null;
        this.remainingHearts = 3;

        this.bindEventListeners();
    }

    bindEventListeners() {
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('option')) {
                this.handleAnswer(e.target);
            }
        });
    }

    async loadExercise() {
        try {
            const response = await fetch(`/ajax/get-exercise.php?lessonId=${this.lessonId}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentExercise = data.exercise;
                this.renderExercise(data.exercise);
            } else {
                this.showLessonComplete();
            }
        } catch (error) {
            console.error('Error loading exercise:', error);
        }
    }

    renderExercise(exercise) {
        let html = '';
        
        switch (exercise.exerciseType) {
            case 'multiple_choice':
                html = this.renderMultipleChoice(exercise);
                break;
            case 'word_match':
                html = this.renderWordMatch(exercise);
                break;
            case 'type_translation':
                html = this.renderTypeTranslation(exercise);
                break;
        }

        this.container.innerHTML = html;
        this.animateExerciseEntry();
    }

    renderMultipleChoice(exercise) {
        return `
            <div class="question">${exercise.question}</div>
            <div class="options">
                ${exercise.options.map(option => `
                    <div class="option" data-value="${option}">
                        ${option}
                    </div>
                `).join('')}
            </div>
        `;
    }

    async handleAnswer(optionElement) {
        const answer = optionElement.dataset.value;
        
        try {
            const response = await fetch('/ajax/check-answer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    exerciseId: this.currentExercise.exerciseId,
                    answer: answer
                })
            });
            
            const result = await response.json();
            
            if (result.correct) {
                this.showSuccess(optionElement, result.feedback);
            } else {
                this.showError(optionElement, result.feedback);
                this.decrementHearts();
            }
        } catch (error) {
            console.error('Error checking answer:', error);
        }
    }

    showSuccess(element, feedback) {
        element.classList.add('correct');
        this.showFeedback(feedback, 'correct');
        this.celebration.style.display = 'block';
        
        setTimeout(() => {
            this.celebration.style.display = 'none';
            this.loadNextExercise();
        }, 1500);
    }

    showError(element, feedback) {
        element.classList.add('wrong');
        this.showFeedback(feedback, 'wrong');
        
        setTimeout(() => {
            element.classList.remove('wrong');
        }, 1000);
    }

    showFeedback(message, type) {
        this.feedback.textContent = message;
        this.feedback.className = `feedback ${type} visible`;
        
        setTimeout(() => {
            this.feedback.classList.remove('visible');
        }, 2000);
    }

    decrementHearts() {
        this.remainingHearts--;
        this.hearts.innerHTML = '❤️'.repeat(this.remainingHearts);
        
        if (this.remainingHearts === 0) {
            this.showLessonFailed();
        }
    }

    animateExerciseEntry() {
        this.container.style.opacity = '0';
        this.container.style.transform = 'translateY(20px)';
        
        requestAnimationFrame(() => {
            this.container.style.transition = 'all 0.3s ease';
            this.container.style.opacity = '1';
            this.container.style.transform = 'translateY(0)';
        });
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.learningInterface = new LearningInterface();
    window.learningInterface.loadExercise();
}); 
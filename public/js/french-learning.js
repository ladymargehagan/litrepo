class FrenchLearning {
    constructor() {
        this.words = initialWords; // From PHP
        this.vocabularyList = vocabularyList; // Full list of words to learn
        this.currentIndex = 0;
        this.loadedWordCount = this.words.length;
        
        // DOM elements
        this.flashcard = document.getElementById('flashcard');
        this.frontSide = document.querySelector('.flashcard-front');
        this.backSide = document.querySelector('.flashcard-back');
        this.exampleSection = document.querySelector('.example-section');
        this.exampleText = document.querySelector('.example-text');
        
        // Buttons
        this.flipBtn = document.getElementById('flipBtn');
        this.audioBtn = document.getElementById('audioBtn');
        this.exampleBtn = document.getElementById('exampleBtn');
        this.hardBtn = document.getElementById('hardBtn');
        this.easyBtn = document.getElementById('easyBtn');
        
        this.bindEvents();
        this.loadWord();
    }

    bindEvents() {
        this.flipBtn.addEventListener('click', () => this.flipCard());
        this.audioBtn.addEventListener('click', () => this.playAudio());
        this.exampleBtn.addEventListener('click', () => this.toggleExample());
        this.hardBtn.addEventListener('click', () => this.markWord('hard'));
        this.easyBtn.addEventListener('click', () => this.markWord('easy'));
    }

    loadWord() {
        const word = this.words[this.currentIndex];
        this.frontSide.textContent = word.french;
        this.backSide.textContent = word.english;
        this.flashcard.classList.remove('flipped');
        
        // Hide example section when loading new word
        this.exampleSection.style.display = 'none';
        
        // Load more words if we're near the end
        if (this.currentIndex >= this.words.length - 2) {
            this.loadMoreWords();
        }
    }

    async loadMoreWords() {
        if (this.loadedWordCount >= this.vocabularyList.length) return;
        
        try {
            const nextWords = this.vocabularyList.slice(
                this.loadedWordCount, 
                this.loadedWordCount + 5
            );
            
            const response = await fetch('/ajax/translate-words.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ words: nextWords })
            });
            
            const newWords = await response.json();
            if (newWords.success) {
                this.words = [...this.words, ...newWords.translations];
                this.loadedWordCount += newWords.translations.length;
            }
        } catch (error) {
            console.error('Error loading more words:', error);
        }
    }

    flipCard() {
        this.flashcard.classList.toggle('flipped');
    }

    toggleExample() {
        const word = this.words[this.currentIndex];
        if (word.examples && word.examples.length > 0) {
            this.exampleText.textContent = word.examples[0];
            this.exampleSection.style.display = 
                this.exampleSection.style.display === 'none' ? 'block' : 'none';
        }
    }

    async playAudio() {
        const word = this.words[this.currentIndex].french;
        const utterance = new SpeechSynthesisUtterance(word);
        utterance.lang = 'fr-FR';
        speechSynthesis.speak(utterance);
    }

    async markWord(difficulty) {
        const word = this.words[this.currentIndex];
        
        try {
            const response = await fetch('/ajax/mark-learned-word.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    english: word.english,
                    french: word.french,
                    difficulty: difficulty
                })
            });
            
            const data = await response.json();
            if (data.success) {
                // Update progress bar
                document.querySelector('.progress').style.width = `${data.progress}%`;
                document.querySelector('.progress-text').textContent = 
                    `${data.learned_count} words learned`;
                
                // Move to next word
                this.currentIndex = (this.currentIndex + 1) % this.words.length;
                this.loadWord();
            }
        } catch (error) {
            console.error('Error marking word:', error);
        }
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new FrenchLearning();
}); 
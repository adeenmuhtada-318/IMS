/**
 * THEME SWITCHING ARCHITECTURE
 * Handles operational mode toggling between Tactical Cyber Dark and Corporate Light.
 */

const themeSwitcher = document.getElementById('themeSwitcher');
const rootElement = document.documentElement;

const toggleTheme = () => {
    const currentTheme = rootElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    rootElement.setAttribute('data-theme', newTheme);
    
    // Optional: Persist user preference
    localStorage.setItem('imsThemePreference', newTheme);
};

// Initialize theme from storage if available
const savedTheme = localStorage.getItem('imsThemePreference');
if (savedTheme) {
    rootElement.setAttribute('data-theme', savedTheme);
}

// Event Listeners
if (themeSwitcher) {
    themeSwitcher.addEventListener('click', toggleTheme);
}

// Log System Initialization
console.log('Tactical Matrix Interface Initialized');

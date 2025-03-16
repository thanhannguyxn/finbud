document.addEventListener('DOMContentLoaded', function () {
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const micButton = document.getElementById('voice-button');
    
    let categories = {};
    let subCategories = {};
    let isProcessing = false; // To prevent duplicate input processing
    
    // Check if the browser supports the Web Speech API
    const recognition = window.SpeechRecognition || window.webkitSpeechRecognition ? new (window.SpeechRecognition || window.webkitSpeechRecognition)() : null;
    if (!recognition) {
        console.warn('Speech recognition not supported in this browser.');
    }
    
    // Send a greeting message when chatbot is opened
    function greetUser() {
        chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> Hello! I'm your FinBud assistant. You can add expenses by typing something like: "I spent 200 on Beverages today." How can I help you today?</div>`;
    }
    
    // Initialize greeting
    greetUser();
    
    // Load main categories and sub-categories
    fetch('fetch_main_categories.php')
    .then(response => response.json())
    .then(data => {
        categories = data.reduce((obj, item) => {
            obj[item.category_name.toLowerCase()] = item.category_id;
            return obj;
        }, {});
    });
    
    fetch('fetch_sub_categories.php?category_id=0')
    .then(response => response.json())
    .then(data => {
        subCategories = data.reduce((obj, item) => {
            obj[item.sub_category_name.toLowerCase()] = item;
            return obj;
        }, {});
    });
    
    // Handle user message input
    chatbotInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && !isProcessing) {
            processUserMessage(chatbotInput.value.trim());
            chatbotInput.value = '';
        }
    });
    
    // Handle microphone input
    micButton.addEventListener('click', function () {
        if (!recognition) {
            chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> Voice input is not supported in this browser.</div>`;
            return;
        }
        
        recognition.start();
        chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> Listening...</div>`;
        
        recognition.onresult = (event) => {
            const voiceInput = event.results[0][0].transcript;
            
            // Prevent duplicate processing
            if (!isProcessing) {
                processUserMessage(voiceInput);
            }
        };
        
        recognition.onerror = (event) => {
            chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> Could not understand. Please try again.</div>`;
            console.error('Speech recognition error:', event.error);
        };
    });
    
    // Process user messages (text or voice)
    function processUserMessage(userMessage) {
        // Check for greetings
        if (isGreeting(userMessage)) {
            chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> Hi there! How can I assist you with your expenses today?</div>`;
            isProcessing = false;
            return;
        }
        isProcessing = true; // Mark as processing to avoid duplicates
        chatbotMessages.innerHTML += `<div><strong>You:</strong> ${userMessage}</div>`;
        
        const parsedData = parseMessage(userMessage);
        
        if (!parsedData.amount || !parsedData.sub_category_name) {
            chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> Could not parse your input. Please try again with a format like: "I spent 200 on Beverages today."</div>`;
            isProcessing = false; // Reset processing flag
            return;
        }
        
        // Send data to the backend
        fetch('expense_api/chatbot_add_expense.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(parsedData)
        })
        .then(response => response.json())
        .then(data => {
            chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> ${data.message}</div>`;
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            isProcessing = false; // Reset processing flag
        })
        .catch(() => {
            chatbotMessages.innerHTML += `<div><strong>Chatbot:</strong> Something went wrong. Try again!</div>`;
            isProcessing = false; // Reset processing flag
        });
    }
    
    // Calculate Levenshtein Distance
    function levenshteinDistance(a, b) {
        const matrix = [];
        
        // Create matrix
        for (let i = 0; i <= b.length; i++) {
            matrix[i] = [i];
        }
        for (let j = 0; j <= a.length; j++) {
            matrix[0][j] = j;
        }
        
        // Compute distances
        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1, // Substitution
                        matrix[i][j - 1] + 1,     // Insertion
                        matrix[i - 1][j] + 1      // Deletion
                    );
                }
            }
        }
        
        return matrix[b.length][a.length];
    }
    
    // Find closest sub-category using Levenshtein Distance
    function findClosestSubCategory(input) {
        const normalizedInput = input.toLowerCase();
        let closestMatch = null;
        let smallestDistance = Infinity;
        
        for (const subCat in subCategories) {
            const distance = levenshteinDistance(normalizedInput, subCat.toLowerCase());
            if (distance < smallestDistance) {
                smallestDistance = distance;
                closestMatch = subCat;
            }
        }
        
        // If distance is within threshold, return closest match
        if (smallestDistance <= 2) {
            return closestMatch;
        }
        
        return null; // No close match found
    }
    
    function parseMessage(message) {
        const amountMatch = message.match(/\b\d+(\.\d+)?\b/);
        const amount = amountMatch ? parseFloat(amountMatch[0]) : null;
        
        const dateMatch = message.match(/\b(today|yesterday|(\d{4}-\d{2}-\d{2}))\b/i);
        let expense_date = new Date().toISOString().split('T')[0];
        
        if (dateMatch) {
            if (dateMatch[1]?.toLowerCase() === 'yesterday') {
                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                expense_date = yesterday.toISOString().split('T')[0];
            } else if (dateMatch[2]) {
                expense_date = dateMatch[2];
            }
        }
        
        // Find closest sub-category
        const words = message.split(' ');
        let subCategoryName = null;
        
        for (const word of words) {
            subCategoryName = findClosestSubCategory(word);
            if (subCategoryName) break;
        }
        
        let description = message;
        return {
            amount: amount,
            sub_category_name: subCategoryName,
            description: description,
            expense_date: expense_date
        };
    }
    
    // Check if the message is a greeting
    function isGreeting(message) {
        const greetings = ["hello", "hi", "hey", "chào", "good morning", "good afternoon", "good evening"];
        return greetings.some(greet => message.toLowerCase().includes(greet));
    }
});

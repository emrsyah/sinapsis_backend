<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Whisper Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        textarea { width: 100%; height: 200px; padding: 1rem; margin-top: 1rem; font-size: 1rem; }
        .status { margin-top: 10px; color: gray; font-style: italic; min-height: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>WebSocket Whisper Test</h1>
        <p>Testing Private Channel: <strong>note.{{ $note->id }}</strong></p>
        <p>Open this page in two different tabs or browsers to test whispering.</p>

        <div>
            <textarea id="noteContent" placeholder="Type something here...">{{ $note->content }}</textarea>
        </div>
        <div id="status" class="status"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const noteId = '{{ $note->id }}';
            const textarea = document.getElementById('noteContent');
            const statusDiv = document.getElementById('status');
            let typingTimer;

            // Wait a moment for Echo to initialize
            setTimeout(() => {
                if (window.Echo) {
                    const channel = window.Echo.private(`note.${noteId}`);

                    // Listen for whispers
                    channel.listenForWhisper('typing', (e) => {
                        console.log('Received whisper:', e);
                        statusDiv.textContent = 'Someone is typing...';
                        
                        // Optionally update textarea if you want it to sync completely
                        textarea.value = e.content;

                        clearTimeout(typingTimer);
                        typingTimer = setTimeout(() => {
                            statusDiv.textContent = '';
                        }, 2000);
                    });

                    // Emit whispers on input
                    textarea.addEventListener('input', (e) => {
                        channel.whisper('typing', {
                            content: e.target.value
                        });
                    });
                } else {
                    statusDiv.textContent = 'Echo is not loaded yet.';
                }
            }, 1000);
        });
    </script>
</body>
</html>

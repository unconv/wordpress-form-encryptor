const submit_button = document.getElementById('submit');
submit_button.addEventListener('click', () => {
    const input = document.getElementById('private-key-file');
    const text_to_decrypt = document.getElementById('text-to-decrypt');
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.readAsText(file);
        reader.onload = () => {
            const contents = reader.result;
            // POST the contents of the file to the server
            fetch('/wp-admin/admin.php?wp_form_encryptor_action=decrypt', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "key="+encodeURIComponent(contents)+"&text="+encodeURIComponent(text_to_decrypt.value),
            })
            .then(response => response.text())
            .then(text => {
                // Update the "decrypted-text" element with the response from the server
                document.getElementById('decrypted-text').innerHTML = "Decrypted: " + text;
                text_to_decrypt.value = "";
            });
        }
    }
});

function getOrCreateStyleWrapper() {
    const editor = document.getElementById('editor');
    let wrapper = editor.querySelector(':scope > #content-style-wrapper');
    if (!wrapper) {
        wrapper = document.createElement('span');
        wrapper.id = 'content-style-wrapper';
        while (editor.firstChild) {
            wrapper.appendChild(editor.firstChild);
        }
        editor.appendChild(wrapper);
    }
    return wrapper;
}

function formatText(command) {
    document.execCommand(command, false, null);
    document.getElementById('editor').focus();
}

function formatFont(font) {
    const wrapper = getOrCreateStyleWrapper();
    wrapper.style.fontFamily = font;
    document.getElementById('editor').focus();
}

function formatSize(size) {
    const wrapper = getOrCreateStyleWrapper();
    wrapper.style.fontSize = size;
    document.getElementById('editor').focus();
}

function insertHR() {
    document.execCommand('insertHTML', false, '<hr style="border:1px solid #334155; margin:16px 0;">');
    document.getElementById('editor').focus();
}

document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('konten_teks').value = document.getElementById('editor').innerHTML;
});

document.getElementById('editor').addEventListener('focus', function() {
    if (this.innerHTML === '<p>Tulis isi chapter di sini...</p>') {
        this.innerHTML = '';
    }
});
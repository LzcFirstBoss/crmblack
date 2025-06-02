const btnEmoji = document.getElementById('btnEmoji');
const emojiPickerContainer = document.getElementById('emojiPicker');
const inputMensagemEmoji = document.getElementById('input-mensagem');

// Toggle do picker
btnEmoji.addEventListener('click', (e) => {
    e.stopPropagation();
    if (emojiPickerContainer.classList.contains('hidden')) {
        emojiPickerContainer.classList.remove('hidden');
        emojiPickerContainer.innerHTML = '';

        const picker = new EmojiMart.Picker({
            onEmojiSelect: (emoji) => {
                insertEmoji(emoji.native);
                // NÃO FECHA MAIS AO ESCOLHER O EMOJI
            },
            set: 'apple',
            locale: 'pt'
        });

        emojiPickerContainer.appendChild(picker);
    } else {
        emojiPickerContainer.classList.add('hidden');
    }
});

// Fechar ao clicar fora
document.addEventListener('click', (e) => {
    if (!emojiPickerContainer.contains(e.target) && e.target !== btnEmoji) {
        emojiPickerContainer.classList.add('hidden');
    }
});

function insertEmoji(emoji) {
    inputMensagemEmoji.focus();

    const range = document.createRange();
    range.selectNodeContents(inputMensagemEmoji);
    range.collapse(false);

    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);

    const emojiNode = document.createTextNode(emoji);
    range.insertNode(emojiNode);

    range.setStartAfter(emojiNode);
    sel.removeAllRanges();
    sel.addRange(range);
}
import { sendRequest } from '../utils.js';

export default {
    delimiters: ['[[', ']]'],
    template: `
        <div class="html-editor">
            <h2>HTML Редактор</h2>
            <textarea id="code-editor"></textarea>
            <button @click="saveContent" class="save-button">Сохранить</button> <!-- Кнопка сохранения -->
        </div>
    `,
    mounted() {
        this.editor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
            mode: 'htmlmixed',
            lineNumbers: true,
            theme: 'default',
        });
        this.loadContent(); // Загружаем содержимое шаблона письма при монтировании
    },
    data() {
        return {
            editor: null
        };
    },
    methods: {
        getEditorContent() {
            return this.editor.getValue();
        },
        setEditorContent(content) {
            this.editor.setValue(content);
        },
        loadContent() {
            // Загружаем содержимое из window.ardozlock.emailTemplateContent
            const content = window.ardozlock.emailTemplateContent || '';
            this.setEditorContent(content);
        },
        saveContent() {
            const content = this.getEditorContent();
            console.log("Содержимое для сохранения:", content);

            sendRequest('/ardozlock/saveemailtemplate/', { content })
                .then(data => {
                    alert('Содержимое успешно сохранено!');
                })
                .catch(error => {
                    console.error('Ошибка при сохранении содержимого:', error);
                    alert('Произошла ошибка при сохранении содержимого.');
                });
        }
    }
};

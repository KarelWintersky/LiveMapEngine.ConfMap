/**
 * Устанавливает у блока SELECT OPTION опцию, соответствующую значению data-атрибута `data-selected`
 *
 * Нужно для того, чтобы у <option> ставился selected на фронте, а не строился вырвиглазной конструкцией в шаблоне
 */
class SelectUpdater {
    constructor(selector) {
        this.selectElements = document.querySelectorAll(selector);
    }

    update() {
        this.selectElements.forEach((select) => {
            let selectedOption = select.dataset['selected'];
            select.querySelectorAll('option').forEach((option) => {
                if (option.value === selectedOption || (!selectedOption && option.value === "")) {
                    option.selected = true;
                }
            });
        });
    }
}

// -eof- //
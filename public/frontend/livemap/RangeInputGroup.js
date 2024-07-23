/**
 * Синхронное поведение input-range и текстового поля
 * Вызов:
 * document.addEventListener('DOMContentLoaded', function() { new RangeInputGroup('lsi_slider'); });
 *
 * Поддерживает цвета (но не в полноценной реализации, так чтобы индивидуально для каждого поля ввода, а глобально
 * Это требует доработки... как-нибудь потом (2024-07-23)
 */
class RangeInputGroup {
    styles_common = `
    `
    styles = `
    input[type='range'] {
            -webkit-appearance: none;
            height: 15px;
            border-radius: 5px;
            background: #d3d3d3;
            outline: none;
            opacity: 0.7;
            -webkit-transition: .2s;
            transition: opacity .2s;
        }

        input[type='range']:hover {
            opacity: 1;
        }

        input[type='range']::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
        }

        input[type='range']::-moz-range-thumb {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
        }
    `
    useColorGradient = false

    groups = {}

    /**
     * @todo: хранить опции и прочие параметры в структуре groups - так, чтобы можно было индивидуализировать настройки
     * Кроме того, в опции перенести цвета
     *
     * А еще стили подгружать в зависимости от опции.
     *
     * @param group
     * @param options
     * @returns {boolean}
     */
    constructor(group = '', options = {}) {
        if (group.length < 1) {
            return false;
        }

        if (options.hasOwnProperty('useColorGradient')) {
            this.useColorGradient = options.useColorGradient;
        }

        if (this.useColorGradient) {
            let style = document.createElement('style');
            if (style.styleSheet) {
                style.styleSheet.cssText = this.styles;
            } else {
                style.appendChild(document.createTextNode(this.styles));
            }
            document.getElementsByTagName('head')[0].appendChild(style);
        }

        this.init(group);
    }

    init(group) {
        let that = this;

        // на текстовом поле: изменяем, вводим, вставляем из буфера обмена
        document.querySelectorAll(`input[type="text"][data-ranged="${group}"]`).forEach((input) => {
            input.addEventListener('change', this.groupRangedInputOnChange.bind(this));
            input.addEventListener('keyup', this.groupRangedInputOnChange.bind(this));
            input.addEventListener('paste', this.groupRangedInputOnChange.bind(this));
        });

        // на поле range: меняем, двигаем мышкой
        document.querySelectorAll(`input[type="range"][data-ranged="${group}"]`).forEach((input) => {
            input.addEventListener('change', this.groupRangedInputOnChange.bind(this));
            input.addEventListener('mousemove', this.groupRangedInputOnChange.bind(this));

            if (that.useColorGradient) {
                let colorFrom = input.getAttribute('data-color-from');
                let colorTo = input.getAttribute('data-color-to');
                let min = input.getAttribute('min') || 0;
                let max = input.getAttribute('max') || 10;

                input.style.background = that.colorRange(min, max, colorFrom, colorTo, input.value);
            }
        });
    }

    groupRangedInputOnChange(event) {
        let group = event.target.getAttribute('data-ranged');
        let type = event.target.type === 'range' ? 'text' : 'range';
        let value = event.target.value;
        let input = document.querySelector(`input[type="${type}"][data-ranged="${group}"]`);
        input.value = value;

        if (this.useColorGradient) {
            let ranged = document.querySelector(`input[type="range"][data-ranged="${group}"]`);

            let colorFrom = ranged.getAttribute('data-color-from');
            let colorTo = ranged.getAttribute('data-color-to');
            let step = ranged.getAttribute('step') || 1;
            let min = ranged.getAttribute('min') || 0;
            let max = ranged.getAttribute('max') || 10;

            let colorRange = this.colorRange(min, max, colorFrom, colorTo, value);

            ranged.style.backgroundColor = colorRange;
        }
    }

    colorRange(min, max, colorFrom, colorTo, value) {
        let colorArrayFrom = colorFrom.split(',');
        let colorArrayTo = colorTo.split(',');
        let r = Math.round( this.calculateColor(value, min, max, colorArrayFrom[0], colorArrayTo[0]));
        let g = Math.round( this.calculateColor(value, min, max, colorArrayFrom[1], colorArrayTo[1]));
        let b = Math.round( this.calculateColor(value, min, max, colorArrayFrom[2], colorArrayTo[2]));
        return `rgb(${r}, ${g}, ${b})`;
    }

    calculateColor(value, min, max, A, B) {
        let ratio = (value - min) / (max - min);
        return (A > B) ? A - ratio * (A - B) : A + ratio * (B - A);
    }

}

// -eof- //
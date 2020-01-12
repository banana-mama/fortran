console.log(1337);
throw new Error();


//
function count(object) {
    let size = 0, key;
    for (key in object) {
        if (object.hasOwnProperty(key)) size++;
    }
    return size;
}


// TODO
function isset(value) {
    return (value !== undefined);
}


//
class Canvas {


    //
    constructor(size, background) {

        this.rawData = [];
        this.background = [0, 0, 0];
        this.size = {'w': 40, 'h': 40};

        if (size === undefined) this.size = {'w': 40, 'h': 40};
        else this.size = size;

        if (background === undefined) this.background = [0, 0, 0];
        else this.background = background;

        this.fill();

    }


    //
    fill() {

        let height = this.size['h'];
        let width = this.size['w'];

        for (let y = 1; y <= height; y++) {
            for (let x = 1; x <= width; x++) {
                let xy = {'x': x, 'y': y};
                this.setPixel(xy, this.background)
            }
        }

    }


    //
    setPixel(position, pixelColor) {
        for (let i = 0; i < count(pixelColor); i++) {
            let index = this.calculateIndex(position, i);
            this.rawData[index] = this.pixelColor(i);
        }
    }


    //
    calculateIndex(position, colorIndex) {

        let x = (position['x'] - 1);
        let y = (position['y'] - 1);
        let colorsCount = count(this.background);

        let rowAndColumnOffset = ((this.size['w'] * y) + x);
        return ((rowAndColumnOffset * colorsCount) + colorIndex)

    }


    //
    setTriangle(positionA, positionB, positionC, colors) {

        if (colors === undefined) {
            colors = [[255, 255, 255], [255, 255, 255]];
        }

        // сортируем координаты 'y' от самой "высокой" до самой "низкой" на канвасе
        let unsortedPositions = [positionA, positionB, positionC];
        unsortedPositions.sort(function (a, b) {
            if (a['y'] === b['y']) return 0;
            return ((a['y'] > b['y']) ? 1 : -1);
        });

        let positions = {'A': null, 'B': null, 'C': null};
        let i = 0;
        for (let key in positions) {
            if (positions.hasOwnProperty(key)) {
                positions[key] = unsortedPositions[i];
                i++;
            }
        }

        let Sy = Math.floor(positions['A']['y']);
        let Ey = Math.ceil(positions['C']['y']);

        // Рисуем половинки треугольника

        let colorsIsset = null;
        if (isset(colors[0])) colorsIsset = colors[0];

        let roundBy =  Math.round(positions['B']['y']);
        this.setTriangleHalf(positions, Sy, roundBy, function (positions, y) {
            y = (y + 0.5);

            let data = {'Sx': this.getIntersectionPoint(positions['A'], positions['B'], y)};

            if (isset(positions['A']['r']) && isset(positions['B']['r'])) {
                data['Sx'] = this.getIntersectionPoint(positions['A'], positions['B'], y, 'r');
            }
            if (isset(positions['A']['g']) && isset(positions['B']['g'])) {
                data['Sg'] = this.getIntersectionPoint(positions['A'], positions['B'], y, 'g');
            }
            if (isset(positions['A']['b']) && isset(positions['B']['b'])) {
                data['Sb'] = this.getIntersectionPoint(positions['A'], positions['B'], y, 'b');
            }

            return data;
        }, colorsIsset);

        //

        colorsIsset = null;
        if (isset(colors[1])) colorsIsset = colors[1];

        roundBy = (roundBy + 1);
        this.setTriangleHalf(positions, roundBy, Ey, function (positions, y) {
            y = (y + 0.5);

            let data = {'Sx': this.getIntersectionPoint(positions['B'], positions['C'], y)};

            if (isset(positions['B']['r']) && isset(positions['C']['r'])) {
                data['Sx'] = this.getIntersectionPoint(positions['B'], positions['C'], y, 'r');
            }
            if (isset(positions['B']['g']) && isset(positions['C']['g'])) {
                data['Sg'] = this.getIntersectionPoint(positions['B'], positions['C'], y, 'g');
            }
            if (isset(positions['A']['b']) && isset(positions['B']['b'])) {
                data['Sb'] = this.getIntersectionPoint(positions['B'], positions['C'], y, 'b');
            }

            return data;
        }, colorsIsset);

        //

    }


    // TODO
    setTriangleHalf(positions, from, to, SxCallback, color) {
        if (color === undefined) {
            color = [255, 255, 255];
        }

        for (let y = from; y <= to; y++) {

            let data = SxCallback(positions, y);
            let Sx = data['Sx'];

            if (isset(data['Sr'])) let Sr = data['Sr'];
            if (isset(data['Sg'])) let Sg = data['Sg'];
            if (isset(data['Sb'])) let Sb = data['Sb'];

            let yPlus05 = (y + 0.5);
            let Ex = this.getIntersectionPoint(positions['A'], positions['C'], yPlus05);

            if (isset(positions['A']['r']) && isset(positions['C']['r'])) {
                let Er = this.getIntersectionPoint(positions['A'], positions['C'], yPlus05, 'r');
            }
            if (isset(positions['A']['g']) && isset(positions['C']['g'])) {
                let Eg = this.getIntersectionPoint(positions['A'], positions['C'], yPlus05, 'g');
            }
            if (isset(positions['A']['b']) && isset(positions['C']['b'])) {
                let Eb = this.getIntersectionPoint(positions['A'], positions['C'], yPlus05, 'b');
            }

            if (Sx > Ex) {
                let object = {'a': Sx, 'b': Ex};
                this.swap(object);
                Sx = object['a'];
                Ex = object['b'];
            }
            Sx = Math.floor(Sx);
            Ex = Math.floor(Ex);

            for (let x = Sx; x <= Ex; x++) {

                if (isset(Sr) && isset(Sg) && isset(Sb) && isset(Er) && isset(Eg) && isset(Eb)) {

                    let dividend = ((x + 0.5) - Sx);
                    let divider = (Ex - Sx);
                    if (Ex === Sx) divider = 1;

                    let r = (Sr + ((Er - Sr) * (dividend / divider)));
                    let g = (Sg + ((Eg - Sg) * (dividend / divider)));
                    let b = (Sb + ((Eb - Sb) * (dividend / divider)));

                    color = [r, g, b];

                }

                let pixelPositions = {'x': x, 'y' : y};
                this.setPixel(pixelPositions, color);

            }

        }
    }


    // TODO
    getIntersectionPoint(positionA, positionB, value, key) {
        if (key === undefined) {
            key = [255, 255, 255];
        }

        let dividend = (value - positionA['y']);
        let divider = (positionB['y'] - positionA['y']);

        return (positionA[key] + (positionB[key] - positionA[key]) * (dividend / divider));
    }


    //
    swap(object) {
        let temp = object['a'];
        object['a'] = object['b'];
        object['b'] = temp;
    }


}
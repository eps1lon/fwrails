'use strict';

var Distribution = function (rand) {
    var argument;

    this.rand = rand;
    
    if (arguments.length > 1) {
        for (argument in arguments[1]) {
            this[argument] = arguments[1][argument];
        }
    }
};

Distribution.prototype.build = function (min, max, grouping, count, factors) {
    var i = count,
        length = 0,
        number = 0,
        numbers = [],
        samples = {};

    for (i; i > 0; i -= 1) {
        number = Math.round(this.rand(factors) * Math.abs(max - min) + min);
        number -= number % (grouping || 1);
        numbers.push(number);
    }
    
    max = numbers[0],
    min = max;
    
    for (i = 0, length = numbers.length; i < length; i += 1) {
        if (samples[numbers[i]] === undefined) {
            samples[numbers[i]] = 0;
        }

        if (numbers[i] > max) {
            max = numbers[i];
        } else if (numbers[i] < min) {
            min = numbers[i];
        }

        samples[numbers[i]] += 1;
    }

    numbers = [];
    for (i in samples) {
        numbers.push([+i, samples[i] / count]);
    }

    return numbers;
}

var distributions = {
    uniform: new Distribution(function () {
        return Math.random();
    }, {
        toString: function () {
            return 'Gleichverteilung';
        }
    }),
    normal_box: new Distribution(function () {
        var number = Math.sqrt(-2 * Math.log(Math.random())) * Math.sin(2 * Math.random() * Math.PI);
        
        return (number + 4) / 8;
    }, {
        toString: function () {
            return 'sqrt(-2 * ln(x)) * sin(2Pi * x';
        }
    }),
    normal_polar: new Distribution(function () {
        var q = 0,
            u1, u2, p;

        do {
            u1 = Math.random() * 2 - 1;
            u2 = Math.random() * 2 - 1;
            q = Math.pow(u1, 2) + Math.pow(u2, 2);
        } while (q <= 0 || q > 1);

        p = Math.sqrt((-2 * Math.log(q)) / q);

        return Math.exp(u1 * p);
    }, {
        toString: function () {
            return 'q = sqrt(x1^2 + x2^2)\ne^sqrt(-2ln(q) / q';
        }
    }),
    exponentiel: new Distribution(function (factors) {
        var a = factors.a || -2;
        return Math.exp(a * Math.random());
    }, {
        toString: function () {
            return 'e^a*x';
        }
    }),
    lognormal: new Distribution(function (factors) {
        var a = factors.a || -2;
        return (Math.sqrt(a * Math.log(Math.random())) / 4.5);
    }, {
        toString: function () {
            return 'sqrt(a * ln(x) / 4.5)';
        }
    }),
    square: new Distribution(function (factors) {
        var a = factors.a || 1,
            b = factors.b || 0;
        return 1 - Math.pow(a * Math.random(), 1/2);
    }, {
        toString: function () {
            return 'ax^2 + bx';
        }
    }),
    trend: new Distribution(function () {
        var min = Math.random(),
            new_min = 0,
            i, length = 3;
        
        for (i = 1; i <= length; i += 1) {
            new_min = Math.random();
            if (new_min < min) {
                min = new_min;
            }
        }
        
        return min;
    }, {
        toString: function () {
            return 'ax^2 + bx';
        }
    })
};
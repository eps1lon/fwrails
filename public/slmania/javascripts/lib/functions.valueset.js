function find (haystack, needle, y) {
    var key = -1;
    if (typeof y === 'undefined') {
        y = 0;
    }
    
    $.each(haystack, function (i, value) {
        if (value[y] === needle) {
            key = i;
            return false;
        }
        
        return true;
    });

    return key;
}

function extrema (arr) {
    var i = 1,
        length = arr.length,
        min = [0, 0],
        max = [0, 0];

    if (length > 0) {
        min = [0, arr[0][0]];
        max = [0, arr[0][0]];

        for (i; i < length; i += 1) {
            if (arr[i][0] > max[1]) {
                max = [i, arr[i][0]];
            } else if (arr[i][0] < min[1]) {
                min = [i, arr[i][0]];
            }
        }
    } 

    return {min: min, max: max};
}

function push (arr, value, y) {
    if (typeof y === 'undefined') {
        y = 1;
    }
    
    var key = find(arr, value, 1 - y),
        add = [];
 
    if (key === -1) {
        add[y] = 1;
        add[1 - y] = value;
        return (arr.push(add) - 1);
    } else {
        arr[key][y] += 1;
    }

    return key;
}

function prob (arr) {
    var i = 0,
        length = arr.length,
        sum = summarize(arr),
        e = 0;

    for (i; i < length; i += 1) {
        arr[i][1] /= sum;
        
        e += arr[i][0] * arr[i][1];
    }

    return e;
}

function summarize (arr) {
    var i = 0,
        length = arr.length,
        sum = 0;

    for (i; i < length; i += 1) {
        sum += arr[i][1];
    }

    return sum;
}

function erwartungswert (arr) {
    return summarize(arr) / arr.length;
}
// copy of compareObjects function in
// TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader module
export function compareObjects (obj1, obj2) {
    var type1 = typeof obj1;
    var type2 = typeof obj2;
    if (type1 !== type2) {
        return false;
    }

    if (Array.isArray(obj1)) {
        if (obj1.length !== obj2.length) {
            return false;
        }
        for (var x = 0; x < obj1.length; x++) {
            var found = false;
            for (var y = 0; y < obj2.length; y++) {
                if (compareObjects(obj1[x], obj2[y])) {
                    found = true;
                    break;
                }
            }
            if (!found) {
                return false;
            }
        }
    } else if (type1 === 'object') {
        var keys1 = Object.keys(obj1);
        var keys2 = Object.keys(obj2);

        if (keys1.length !== keys2.length) {
            return false;
        }
        for (var i = 0; i < keys1.length; i++) {
            if (!compareObjects(obj1[keys1[i]], obj2[keys1[i]])) {
                return false;
            }
        }
    } else {
        return obj1 === obj2;
    }
    return true;
}

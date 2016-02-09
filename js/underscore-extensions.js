(function (_) {
  'use strict';

  _.mixin({

    /**
     * Lookup deep nested object property
     *
     * @param {object} obj
     * @param {string} key (deep nested)
     * @param {*} value (set value)
     * @example _.lookup(obj, 'error.errorMessage')
     * @returns {undefined|*}
     */
    lookup: function (obj, key, value) {
      var keys = key.replace(/\[(["']?)([^\1]+?)\1?\]/g, '.$2').replace(/^\./, '').split('.'),
          root,
          i = 0,
          n = keys.length;

      if (arguments.length > 2) {
        root = obj;
        n--;
        while (i < n) {
          key = keys[i++];
          obj = obj[key] = _.isObject(obj[key]) ? obj[key] : {};
        }
        obj[keys[i]] = value;
        value = root;
      } else {
        while ((obj = obj[keys[i++]]) != null && i < n) {}
        value = i < n ? void 0 : obj;
      }
      return value;
    }
  });

})(window._);

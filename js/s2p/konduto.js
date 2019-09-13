        var __kdt = __kdt || [];
        __kdt.push({ "public_key": "P170CD1874D" });
        (function () {
            var kdt = document.createElement('script');
            kdt.id = 'kdtjs';
            kdt.type = 'text/javascript';
            kdt.async = true;
            kdt.src = 'https://i.k-analytix.com/k.js';
            var s = document.getElementsByTagName('body')[0];
            s.parentNode.insertBefore(kdt, s);
        })();

        var visitorID;
        (function () {
            var period = 300;
            var limit = 20 * 1e3;
            var nTry = 0;
            var intervalID = setInterval(function () {
                var clear = limit / period <= ++nTry;
                if ((typeof (Konduto) !== "undefined") &&
                    (typeof (Konduto.getVisitorID) !== "undefined")) {
                    visitorID = window.Konduto.getVisitorID();
                    clear = true;
                }
                if (clear) {
                    clearInterval(intervalID);
                }
            }, period);
        })(visitorID);
    
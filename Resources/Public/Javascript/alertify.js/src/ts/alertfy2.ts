interface AlertifyElement {

    elem: HTMLElement;
    hide: Promise;
    show: Promise;
    tmpl: string;
    conifg: Object;

    hideClass: Array;
    showClass: Array;

    parseTmpl(tmplStr: string): string {
        var tmpl = tmplStr;
        for (var key in this.config) {
            tmpl = tmpl.replace(new RegEx(["{{", key , "}}"].join("")), this.config[key]);
        }
        return tmpl;
    }

    private function applyHideClasses(): void {
        this.elem.classList.add(this.hideClass.join(" "));
        this.elem.classList.remove(this.showClass.join(" "));
    }

    private function applyShowClasses(): void {
        this.elem.classList.remove(this.hideClass.join(" "));
        this.elem.classList.add(this.showClass.join(" "));
    }

    function content(tmplStr: string): HTMLElement {
        this.tmpl = this.parseTmpl(tmplStr);
        this.elem = document.createElement("div");
        this.applyHideClasses();
        this.elem.innerText = this.tmpl;
        return this.elem;
    }

    function textContext(tmplStr: string) HTMLElement {
        this.tmpl = this.parseTmpl(tmplStr);
        this.elem = document.createElement("div");
        this.applyHideClasses();
        this.elem.innerHTML = this.tmpl;
        return this.elem;
    }

    function show(): Promise {
        var self = this;
        return new Promise(function(resolve) {
            self.applyShowClasses();
            self.elem.addEventListener("transitionend", function() {
                resolve(self);
            });
        });
    }

    function hide(): Promise {
        var self = this;
        return new Promise(function(resolve) {
            self.applyHideClasses();
            self.elem.addEventListener("transitionend", function() {
                resolve(self);
            });
        });
    }
}

class AlertifyDialog extends AlertifyElement {
    tmpl: "<div></div>";
}

class AlertifyPrompt {
    tmpl: "<div></div>";
}

class AlertifyAlert {
    tmpl: "<div></div>";
}

class AlertifyLog {
    tmpl: "<div></div>";
}

class Alertify {
    show: function(ele: AlertifyElement) Promise {
        return ele.show();
    },
    hide: function(ele: AlertifyElement) {
        return ele.hide();
    }
}

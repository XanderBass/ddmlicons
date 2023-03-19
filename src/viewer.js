let DDMLIcons = {
    "icon" : {
        "filled"      : false,
        "animated"    : false,
        "select"      : false,
        "name"        : '',
        "orientation" : '',
        "color"       : '',
        "disabled"    : false,
    },
    "ding" : {
        "filled"      : false,
        "animated"    : false,
        "select"      : false,
        "name"        : '',
        "orientation" : '',
        "color"       : '',
        "pos"         : '',
    }
};

HTMLElement.prototype.DDMLIconUpdatePart = function(part) {
    let PART = DDMLIcons[part];
    if (PART.name)        this.classList.add('ddml-' + part + '-' + PART.name);
    if (PART.filled)      this.classList.add('ddml-filled-' + part);
    if (PART.animated)    this.classList.add('ddml-animated-' + part);
    if (PART.orientation) this.classList.add('ddml-orientation-' + part + '-' + PART.orientation);
    if (PART.color)       this.classList.add('ddml-color-' + part + '-' + PART.color);
};

HTMLElement.prototype.DDMLIconUpdate = function() {
    this.setAttribute('class', '');
    this.DDMLIconUpdatePart('icon');
    this.DDMLIconUpdatePart('ding');
    if (DDMLIcons.icon.disabled) this.classList.add('ddml-state-disabled');
    if (DDMLIcons.ding.pos)      this.classList.add('ddml-pos-' + DDMLIcons.ding.pos);
    let CLE = document.getElementById('classlist');
    CLE.innerHTML = '';
    // noinspection JSUnresolvedFunction
    this.classList.forEach(function(v){
        let CVE = document.createElement('div');
        CVE.innerText = v;
        CLE.appendChild(CVE);
    });
    this.classList.add(this.getAttribute('data-size'));
    this.classList.add('icon');
};

HTMLElement.prototype.DDMLToggle = function(classname) {
    let tag = this.tagName;
    this.parentElement.querySelectorAll(tag).forEach(function(e){
        e.classList.remove(classname);
    });
    this.classList.add(classname);
};

function DDMLUpdate() {
    document.querySelectorAll('aside section.icons > div.icon').forEach(function(icon){
        icon.DDMLIconUpdate();
    });
}

document.addEventListener('DOMContentLoaded', function(){
    let MAIN = document.querySelector('main');

    document.querySelectorAll('aside nav.flags a').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            let type = link.getAttribute('data-type');
            let prop = link.getAttribute('data-property');
            DDMLIcons[type][prop] = link.classList.toggle('active');
            DDMLUpdate();
        });
    });

    document.querySelectorAll('aside nav.values a').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            let type = link.parentElement.getAttribute('data-type');
            let prop = link.parentElement.getAttribute('data-property');
            DDMLIcons[type][prop] = link.getAttribute('data-value');
            link.DDMLToggle('active');
            DDMLUpdate();
        });
    });

    document.querySelectorAll('aside nav.select > a').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            let cval = link.classList.contains('active');
            let type = link.getAttribute('data-type');
            DDMLIcons.icon.select = false;
            DDMLIcons.ding.select = false;
            link.parentElement.querySelectorAll('a').forEach(function(e){
                e.classList.remove('active');
            });
            if (!cval) {
                DDMLIcons[type]["select"] = true;
                link.classList.add('active');
                MAIN.classList.add('active');
            } else {
                MAIN.classList.remove('active');
            }
        });
    });

    MAIN.querySelectorAll('ul li').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            let name = link.getAttribute('data-name');
            if (DDMLIcons.icon.select) {
                DDMLIcons.icon.name = name;
                DDMLUpdate();
            }
            if (DDMLIcons.ding.select) {
                DDMLIcons.ding.name = name;
                DDMLUpdate();
            }
            document.querySelectorAll('aside nav.select a').forEach(function(link){
                link.classList.remove('active');
            });
            DDMLIcons.icon.select = false;
            DDMLIcons.ding.select = false;
            MAIN.classList.remove('active');
        });
    });
});
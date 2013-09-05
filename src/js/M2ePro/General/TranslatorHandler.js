var TranslatorHandler = Class.create(Translate,{

    //----------------------------------

    translate : function($super,text) {

        if (!this.data.get(text)) {
            alert('Translation not found : "' + text + '"');
        }

        return $super(text);
    }

    //----------------------------------

});
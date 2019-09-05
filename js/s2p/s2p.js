/**
 *
 * @category   Safe2Pay
 * @package    Safe2Pay_S2P
 * @author     Suporte <integracao@safe2pay.com.br>
 */

var S2P = {
    load: function(url){
        new Ajax.Request(url, {
            onSuccess: function(response) {
                var response = response.responseText.evalJSON();
                if (response.success) {
                    var win = new Window('s2p', {className:'magento', title:'S2P', width:600, height:370, zIndex:1000, opacity:1, destroyOnClose:true, draggable: false, showEffect: Element.show});
                    win.setHTMLContent(response.content_html);
                    win.showCenter(true);
                } else {
                    alert(response.error_message);
                }
            }
        });
        return false;
    }
}

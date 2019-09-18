this.createImagePreview = function(){
    var yOffset = 30;
    $("p.image_preview").hover(function(e) {
        this.imageURL = $(this).attr('ref');
        this.image = new Image();
        this.image.src = this.imageURL;
        
        xOffset = parseInt(this.image.height / 2);
        var xOff = e.pageY - xOffset;
        if (xOff < 0) xOff = 0;
        
        this.t = this.title;
        this.title = "";
        var c = (this.t != "") ? "<br/>" + this.t : "";
        
        $("body").append('<p id="image_preview"><img src="' + $(this).attr('ref') + '" alt="Image image_preview" /><span>' + c + '</span></p>');
        $("#image_preview")
        .css("top",(xOff) + "px")
        .css("left",(e.pageX + yOffset) + "px")
        .fadeIn("fast");
    },
    function() {
        this.title = this.t;	
        $("#image_preview").remove();
    });	
    $("p.image_preview").mousemove(function(e) {
        var xOff = e.pageY - xOffset;
        if (xOff < 0) xOff = 0;
        
        $("#image_preview")
        .css("top",(xOff) + "px")
        .css("left",(e.pageX + yOffset) + "px");
    });			
};

$(document).ready(function(){
	createImagePreview();
});
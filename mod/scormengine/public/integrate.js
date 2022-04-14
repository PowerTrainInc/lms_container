
if(!window.$)
{
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.crossOrigin = "anonymous";
    s.integrity = "sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=";
    document.head.appendChild(s);
    s.onload = function(){

        var s = document.createElement("script");
        s.type = "text/javascript";
        document.head.appendChild(s);
        s.onload = function(){
            //var s = document.createElement("script");
            //s.type = "text/javascript";
            //s.src = "https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js";
            require(window.site_home + "/mod/scormengine/public/bootstrap.bundle.min.js");
            //document.head.appendChild(s);
        }
        s.src = window.site_home + "/mod/scormengine/public/dist/js/app.bundle.js";

    }
    s.src = window.site_home + "/mod/scormengine/public/jquery-3.6.0.min.js";
    
 
    
   // $(document.head).append('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">');
}
else 
{

    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = window.site_home + "/mod/scormengine/public/polyfill.min.js";
    document.head.appendChild(s);
    

    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = window.site_home + "/mod/scormengine/public/dist/js/app.bundle.js";
    document.head.appendChild(s);
}
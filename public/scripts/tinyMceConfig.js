    tinyMCE.init({
                mode : "",
                theme : "advanced",
                plugins : "",
                theme_advanced_buttons1 : "bold,italic,underline,forecolor,backcolor,separator,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink,separator,image",
                theme_advanced_buttons2 : "",
                theme_advanced_buttons3 : "",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_path_location : "bottom",
                extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
                file_browser_callback : "fileBrowserCallBack",
                paste_use_dialog : false,
                theme_advanced_resizing : false,
                theme_advanced_resize_horizontal : false,
                apply_source_formatting : false
            });
            function fileBrowserCallBack(field_name, url, type, win) {
                var connector = "../../filemanager/browser.html?Connector=connectors/php/connector.php";
                var enableAutoTypeSelection = true;     
                var cType;
                tinymcpuk_field = field_name;
                tinymcpuk = win;
                switch (type) {
                    case "image":
                        cType = "Image";
                        break;
                    case "flash":
                        cType = "Flash";
                        break;
                    case "file":
                        cType = "File";
                        break;
                }
                if (enableAutoTypeSelection && cType) { connector += "&Type=" + cType;  }
                    window.open(connector, "tinymcpuk", "modal,width=600,height=400");
            }
var getRectangle = function(img, callback) {
       let canvas = document.createElement('canvas');
       canvas.id = "drawing-canvas";
       canvas.style.position = "absolute";
       canvas.style.left = img.offsetLeft + "px";
       canvas.style.top = img.offsetTop + "px";
       canvas.style.cursor = "crosshair";
       canvas.width = img.clientWidth;
       canvas.height = img.clientHeight;
       document.body.appendChild(canvas);

       let ctx = canvas.getContext('2d');

       canvas.onmousedown = function(e) {
               e.preventDefault();
               e.stopPropagation();

               let x = e.offsetX;
               let y = e.offsetY;

               let width = 0;
               let height = 0;

               canvas.onmouseup = function(e) {
                       e.preventDefault();
                       e.stopPropagation();
               };

               canvas.onmousemove = function(e) {
                       e.preventDefault();
                       e.stopPropagation();

                       width = e.offsetX - x;
                       height = e.offsetY - y;

                       ctx.clearRect(0, 0, canvas.width, canvas.height);
                       ctx.strokeStyle = "green";
                       ctx.strokeRect(x, y, width, height);
                       ctx.strokeStyle = "white";
                       ctx.strokeRect(x + 0.5, y + 0.5, width - 0.5, height - 0.5);

                       canvas.onmouseup = function(e) {
                               e.preventDefault();
                               e.stopPropagation();

                               let x_ratio = img.naturalWidth / img.clientWidth;
                               let y_ratio = img.naturalHeight / img.clientHeight;
                               canvas.remove();

                               if (width < 0) {
                                       width *= -1;
                                       x = x - width;
                               }

                               if (height < 0) {
                                       height *= -1;
                                       y = y - height;
                               }
                               
                               callback({
                                       x: Math.floor(x * x_ratio),
                                       y: Math.floor(y * y_ratio),
                                       width: Math.floor(width * x_ratio),
                                       height: Math.floor(height * y_ratio)
                               });
                       };
               };
       };
}


function resizeImages(images) {
    let imageWidth = '';
    if (images) {
        images.forEach(image => {
            imageWidth = image.offsetWidth;
            image.style.width = '100%';
            image.style.maxWidth = imageWidth + 'px';
            image.style.height = 'auto';
        });
    }
}

function resizeIframes(iframes) {
    let currentIframe = '';
    for(let count = 0; count < iframes.length; count++) {
        currentIframe = allIframes[count];
        currentIframe.id = `iframeResize${count}`;   
        currentIframe.removeAttribute('width');
        currentIframe.removeAttribute('height');
        currentIframe.classList.add('iframe-resized');
        iFrameResize({
           log:false
        },
        `#iframeResize${count}`);
     }
}
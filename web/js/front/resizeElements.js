function resizeImages(images) {
    let imageWidth = '';
    if (images) {
        
        images.forEach(image => {
            imageWidth = Number(image.style.width.replace('px', ''));
            image.style.width = '100%';
            image.style.maxWidth = imageWidth == 0 ? '820px' : imageWidth + 'px';
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
function insertImageIntoModal(e) {
    const isSmartphoneScreen = window.innerWidth < window.innerHeight;
    const isHorizontalImage = this.width > this.height;
    const imageUrl = this.src;
    const modal = document.getElementById('modalShowImage');
    const imageContainer = modal.querySelector('.j-bigImage');
    const image = document.createElement('img');
    const existingImage = imageContainer.querySelector('img');

    image.src = imageUrl;

    if (existingImage) {
        imageContainer.removeChild(existingImage);
    }
    
    imageContainer.appendChild(image);

    if (isSmartphoneScreen && isHorizontalImage) {
        imageContainer.classList.add('rotate');
    } else {
        imageContainer.classList.remove('rotate');
    }
}
function handleSmallImages(images) {
    if (images) {
        images.forEach(image => {
            image.setAttribute('data-toggle', 'modal');
            image.setAttribute('data-target', '#modalShowImage');
            image.addEventListener('click', insertImageIntoModal);
        });
    }
}
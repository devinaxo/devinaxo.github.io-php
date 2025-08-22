function updateTime(){
    let d = new Date().toTimeString().replace(/.*(\d{2}:\d{2}:\d{2}).*/, "$1");
    document.getElementById('time').innerText = d;
}
var players = {};
function onYouTubeIframeAPIReady() {
    $('iframe').each(function() {
        var iframeId = $(this).attr('id');
        players[iframeId] = new YT.Player(iframeId);
    });
}

$(document).ready(function(){
    updateTime();
    setInterval(updateTime, 1000);

    $( ".window" ).draggable({ handle: ".title-bar" });

    const notme = $('#notme');
    $('#cc-btn').on('click', function(){
        if(notme.attr('type') == 'email'){
            notme.attr('type', 'text');
            notme.attr('placeholder', 'Name (Click button to change Cc signature)');
        }else{
            notme.attr('type', 'email');
            notme.attr('placeholder', 'E-mail (Click button to change Cc signature)');
        }
        notme.val('');
    });

    notme.keypress(function(e){
        if(notme.attr('type') == 'text'){
            if(String.fromCharCode(e.keyCode).match(/[^a-zA-Z áéíóú ÁÉÍÓÚ]/gi)) return false;
        }
    });

    const clickableFolders = $('.clickable-folder');
    let currWin;
    let currIcon;
    clickableFolders.on('click', function(){
        $(this).css('background-color', 'gray');
        clickableFolders.not(this).each(function(){
            $(this).css('background-color', 'transparent');
        })
    })

    clickableFolders.on('dblclick', function(){
        $('.needs-closing').hide();
        $('.image-folder').each(function(){
            $(this).attr('src', 'img/directory_closed_cool-0.png');
        })
        $(this).css('background-color', 'transparent');
        currWin = $(this).data('window');
        $('#' + currWin).show();
        currIcon = $(this).data('icon');
        $('#' + currIcon).attr('src', 'img/directory_open_cool-0.png');
    })

    $('.window-close').on('click', function(){
        currWin = $(this).data('window');
        $('#' + currWin).hide();
        currIcon = $(this).data('icon');
        $('#' + currIcon).attr('src', 'img/directory_closed_cool-0.png');
    })

    $('.window-minimize').on('click', function(){
        currWin = $(this).data('window');
        $('#' + currWin).hide();
        currIcon = $(this).data('icon');
        $('#' + currIcon).attr('src', 'img/portfolio-btn.png');
    })
    $('#portfolio-btn').on('click', function(){
        currWin = $(this).data('window');
        $('#' + currWin).show();
        currIcon = $(this).data('icon');
        $('#' + currIcon).attr('src', 'img/portfolio-btn-pressed.png');
    })

    $(document).ready(function() {
        // Handle icon-based modal triggers (existing functionality)
        $('[data-trigger-modal]:not(tr)').each(function() {
            var $iconSpot = $(this);
            var $image = $iconSpot.find('img');
            var modalId = $image.attr('id').replace('image', 'modal');
            var $modal = $('#' + modalId);
            var $modalImg = $modal.find('.modal-content');
            var hqImage = $image.attr('alt');
            var closeId = $image.attr('id').replace('image', 'close');
            var $closeBtn = $('#' + closeId);
            var $iframeContainer = $modal.find('.iframe-container');
            var iframeHtml = $iframeContainer.length ? $iframeContainer.html() : '';
    
            $iconSpot.on('dblclick', function() {
                $modal.show();
                $modalImg.attr('src', hqImage);
            });
            $closeBtn.on('click', function() {
                $modal.hide();
                if ($iframeContainer.length && iframeHtml) {
                    $iframeContainer.html(iframeHtml);
                }
            });
            $iconSpot.on('click', function() {
                $iconSpot.css('background-color', 'unset');
            });
        });

        // Handle table row modal triggers (new functionality)
        $('tr[data-trigger-modal]').each(function() {
            var $row = $(this);
            var modalId = $row.data('modal-id');
            var $modal = $('#' + modalId);
            var $modalImg = $modal.find('.modal-content');
            var $closeBtn = $modal.find('.close');
            var $iframeContainer = $modal.find('.iframe-container');
            var iframeHtml = $iframeContainer.length ? $iframeContainer.html() : '';
            
            // Set image sources based on modal ID
            var imageSrc = '';
            if (modalId === 'modal01') {
                imageSrc = 'img/images/shantien.png';
            } else if (modalId === 'modal02') {
                imageSrc = 'img/images/sniff.jpg';
            }
            
            $row.on('click', function(e) {
                e.preventDefault();
                $modal.show();
                if (imageSrc) {
                    $modalImg.attr('src', imageSrc);
                }
            });
            
            $closeBtn.on('click', function() {
                $modal.hide();
                if ($iframeContainer.length && iframeHtml) {
                    $iframeContainer.html(iframeHtml);
                }
            });
        });

        // Handle modal close when clicking outside
        $('.modal').on('click', function(e) {
            if (e.target === this) {
                $(this).hide();
                var $iframeContainer = $(this).find('.iframe-container');
                if ($iframeContainer.length) {
                    var iframeHtml = $iframeContainer.data('original-html') || $iframeContainer.html();
                    $iframeContainer.html(iframeHtml);
                }
            }
        });
    });

    const btn = document.getElementById('button');
    document.getElementById('contact-form').addEventListener('submit', function(event) {
        event.preventDefault();
        btn.innerHTML = '<img src="img/address_book_card.png" alt="" class="address-sendicon"> Sending...';
        const serviceID = 'service_vzrlfd8';
        const templateID = 'template_g90oli5';
        emailjs.sendForm(serviceID, templateID, this).then(() => {
            btn.innerHTML = '<img src="img/address_book_card.png" alt="" class="address-sendicon"> Thanks for contacting me!';
            $('#contact-form')[0].reset();
            setInterval(() => {
                btn.innerHTML = '<img src="img/address_book_card.png" alt="" class="address-sendicon"> Send';
            }, 2000);
            }, (err) => {
                btn.innerHTML = '<img src="img/address_book_card.png" alt="" class="address-sendicon"> Something went wrong...';
                alert(JSON.stringify(err));
        });
    });

    // FETCHING
    function renderProjectsTable(projects) {
        const tbody = $("#win1 table.interactive tbody");
        tbody.empty();
        projects.forEach(project => {
            let iconPath = project.icon || '';
            if (iconPath && !iconPath.startsWith('img/')) {
                iconPath = 'img/project-icons/' + iconPath;
            }
            const techs = project.technologies.map(t => t.name).join(", ");
            const nameCell = project.url ?
                `<a href="${project.url}" target="_blank" class="flex items-center gap-2 text-black no-underline"><img src="${iconPath}" alt="${project.name} icon" class="w-4 h-4">${project.name}</a>` :
                `<span class="flex items-center gap-2 text-black no-underline"><img src="${iconPath}" alt="${project.name} icon" class="w-4 h-4">${project.name}</span>`;
            tbody.append(`
                <tr>
                    <td>${nameCell}</td>
                    <td>${project.size || ''}</td>
                    <td>${project.type_name || ''}</td>
                    <td>${techs}</td>
                </tr>
            `);
        });
    }

    $.ajax({
        url: 'fetch_projects.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                console.error('API Error:', data.error, data.details || '');
                const tbody = $("#win1 table.interactive tbody");
                tbody.html('<tr><td colspan="4">API Error: ' + (data.error || 'Unknown error') + '<br>' + (data.details || '') + '</td></tr>');
                return;
            }
            console.log('Projects:', data);
            renderProjectsTable(data);
        },
        error: function(xhr, status, error) {
            if (xhr.responseText) {
                try {
                    const errData = JSON.parse(xhr.responseText);
                    errorMsg += '<br>' + (errData.error || '') + '<br>' + (errData.details || '');
                    console.error('AJAX Error:', errData);
                } catch (e) {
                    errorMsg += '<br>' + xhr.responseText;
                    console.error('AJAX Error (raw):', xhr.responseText);
                }
            }
            const tbody = $("#win1 table.interactive tbody");
            tbody.html('<tr><td colspan="4"> Failed to load projects. </td></tr>');
        }
    });
});
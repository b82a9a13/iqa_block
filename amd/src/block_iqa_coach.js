function coach_click_course(id){
    $(`#iqa_dashboard_error_c`)[0].style.display = 'none';
    $(`#iqa_dashboard_content_c`)[0].style.display = 'none';
    $(`.clc`).each(function(){
        $(this)[0].style.display = 'none';
    });
    $(`.clc-${id}`).each(function(){
        $(this)[0].style.display = 'block';
    });
}
function coach_click_learner(id, course){
    const errorText = $(`#iqa_dashboard_error_c`)[0];
    errorText.style.display = 'none';
    const content = $(`#iqa_dashboard_content_c`)[0];
    content.style.display = 'none';
    const xhr = new XMLHttpRequest();
    xhr.open('POST', './../blocks/iqa/classes/inc/block_iqa_coach.inc.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function(){
        if(this.status == 200){
            const text = JSON.parse(this.responseText);
            if(text['error']){
                errorText.innerText = text['error'];
                errorText.style.display = 'block';
            } else if(text['return']){
                content.innerHTML = text['return'];
                content.style.display = 'block';
            } else {
                errorText.innerText = 'Loading error';
                errorText.style.display = 'block';
            }
        } else {
            errorText.innerText = 'Connection error';
            errorText.style.display = 'block';
        }
    }
    xhr.send(`l=${id}&c=${course}`);
}
function iqa_course_content(course, user){
    const errorText = $('#iqa_block_error')[0];
    errorText.style.display = 'none';
    const content = $('#iqa_course_content')[0];
    content.style.display = 'none';
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `./../blocks/iqa/classes/inc/course.inc.php`, true);
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
    xhr.send(`c=${course}&u=${user}`);
}
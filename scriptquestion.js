function toggleCheck(el) {
    let icon = el.querySelector('.check-icon');
    let status = el.querySelector('.check-status');
    if (status.value === "false") {
        icon.style.color = 'green';
        icon.textContent = 'check'; // Change icon text to "check"
        status.value = "true";
    } else {
        icon.style.color = 'red';
        icon.textContent = 'close'; // Change icon text to "close"
        status.value = "false";
    }
}

function addField() {
    const optionList = document.getElementById('optionList');
    const newField = document.createElement('div');
    newField.classList.add('option-container', 'mt-3');
    newField.style.cssText = "border: 1px solid #ccc; border-radius: 5px; padding: 10px; display: flex; align-items: center; margin-top: 10px;";
    newField.innerHTML = `
        <input type="text" class="form-control check-option-text" name="options[]" placeholder="Option" style="margin-bottom: 0; width: auto; flex-grow: 1;" required>
        <span class="toggle-check" style="cursor: pointer; display: inline-flex; align-items: center; margin-left: 10px;" onclick="toggleCheck(this)">
            <i class="material-icons check-icon unchecked" style="color: red;">close</i>
            <input type="hidden" class="check-status" name="checkStatus[]" value="false">
        </span>
        <span class="field-actions" style="display: inline-flex; align-items: center; margin-left: 10px;">
            <div class="icon-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center; margin-right: 5px;">
                <i class="material-icons add-field" style="cursor: pointer; color: blue;" onclick="addField()">add</i>
            </div>
            <div class="icon-container remove-field-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center; margin-right: 5px;">
                <i class="material-icons remove-field" style="cursor: pointer; color: red;" onclick="removeField(this)">delete</i>
            </div>
            <div class="icon-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center; margin-right: 5px;">
                <i class="material-icons move-up" style="cursor: pointer; color: green;" onclick="moveUp(this)">arrow_upward</i>
            </div>
            <div class="icon-container" style="width: 30px; height: 30px; border: 1px solid #ccc; border-radius: 5px; display: flex; justify-content: center; align-items: center;">
                <i class="material-icons move-down" style="cursor: pointer; color: green;" onclick="moveDown(this)">arrow_downward</i>
            </div>
        </span>
    `;
    optionList.appendChild(newField);
    updateRemoveButtons();
}

function removeField(el) {
    el.closest('.option-container').remove();
    updateRemoveButtons();
}

function moveUp(el) {
    let currentField = el.closest('.option-container');
    let previousField = currentField.previousElementSibling;
    if (previousField) {
        currentField.parentNode.insertBefore(currentField, previousField);
    }
}

function moveDown(el) {
    let currentField = el.closest('.option-container');
    let nextField = currentField.nextElementSibling;
    if (nextField) {
        currentField.parentNode.insertBefore(nextField, currentField);
    }
}

function updateRemoveButtons() {
    const optionContainers = document.querySelectorAll('.option-container');
    optionContainers.forEach((container, index) => {
        const removeBtn = container.querySelector('.remove-field-container');
        if (optionContainers.length > 1) {
            removeBtn.style.display = 'flex';
        } else {
            removeBtn.style.display = 'none';
        }
    });
}

function previewImage(event) {
    const imagePreview = document.getElementById('imagePreview');
    const img = document.getElementById('previewImg');
    img.src = URL.createObjectURL(event.target.files[0]);
    imagePreview.style.display = 'block';
}

function previewMusic(event) {
    const musicPreview = document.getElementById('musicPreview');
    const audio = document.getElementById('previewAudio');
    audio.src = URL.createObjectURL(event.target.files[0]);
    musicPreview.style.display = 'block';
}

function previewVideo(event) {
    const videoPreview = document.getElementById('videoPreview');
    const video = document.getElementById('previewVideo');
    video.src = URL.createObjectURL(event.target.files[0]);
    videoPreview.style.display = 'block';
}

function removeImage() {
    const imagePreview = document.getElementById('imagePreview');
    imagePreview.style.display = 'none';
    document.getElementById('uploadImage').value = "";
}

function removeMusic() {
    const musicPreview = document.getElementById('musicPreview');
    musicPreview.style.display = 'none';
    document.getElementById('uploadMusic').value = "";
}

function removeVideo() {
    const videoPreview = document.getElementById('videoPreview');
    videoPreview.style.display = 'none';
    document.getElementById('uploadVideo').value = "";
}

window.onload = function() {
    updateRemoveButtons();
};

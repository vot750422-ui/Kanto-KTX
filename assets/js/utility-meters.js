'use strict';

const utilityRoom = document.querySelector('[data-utility-room]');
if (utilityRoom) {
    utilityRoom.addEventListener('change', () => {
        if (utilityRoom.value !== '') {
            utilityRoom.form?.requestSubmit();
        }
    });
}

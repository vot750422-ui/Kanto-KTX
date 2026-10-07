const building = document.getElementById('building-filter');
const floor = document.getElementById('floor-filter');
if (building && floor) {
    const filterRooms = () => {
        let visible = 0;
        document.querySelectorAll('.room-card').forEach(card => {
            card.hidden = (building.value !== '' && card.dataset.building !== building.value)
                || (floor.value !== '' && card.dataset.floor !== floor.value);
            if (card.hidden) card.querySelector('input').checked = false;
            else visible++;
        });
        document.getElementById('room-empty').hidden = visible > 0;
    };
    building.addEventListener('change', filterRooms);
    floor.addEventListener('change', filterRooms);
}

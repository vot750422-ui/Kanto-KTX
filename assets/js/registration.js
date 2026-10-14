const building = document.getElementById('building-filter');
const floor = document.getElementById('floor-filter');
if (building && floor) {
    const cards = [...document.querySelectorAll('.room-card')].map(card => ({
        card,
        input: card.querySelector('input'),
    }));
    const empty = document.getElementById('room-empty');
    const filterRooms = () => {
        let visible = 0;
        cards.forEach(({ card, input }) => {
            card.hidden = (building.value !== '' && card.dataset.building !== building.value)
                || (floor.value !== '' && card.dataset.floor !== floor.value);
            if (card.hidden && input) input.checked = false;
            else visible++;
        });
        if (empty) empty.hidden = visible > 0;
    };
    building.addEventListener('change', filterRooms);
    floor.addEventListener('change', filterRooms);
}

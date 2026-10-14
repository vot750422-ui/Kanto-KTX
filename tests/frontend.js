'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
let passed = 0;
function check(description, verify) {
    verify();
    passed++;
    process.stdout.write(`[PASS] ${description}\n`);
}
function runAsset(name, context) {
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '..', 'assets', 'js', name), 'utf8'), context);
}

const formEvents = {};
const pageEvents = {};
const submit = { disabled: false };
const disabledByServer = { disabled: true };
const form = {
    dataset: {},
    addEventListener: (event, handler) => { formEvents[event] = handler; },
    querySelectorAll: () => [submit, disabledByServer],
};
const dialog = { open: false, showModal() { this.open = true; }, close() { this.open = false; } };
const openButton = { dataset: { open: 'test-dialog' }, addEventListener: (_, handler) => { openButton.click = handler; } };
const closeButton = { closest: () => dialog, addEventListener: (_, handler) => { closeButton.click = handler; } };
runAsset('approval.js', {
    document: {
        querySelectorAll: selector => ({ '[data-open]': [openButton], '[data-close]': [closeButton], 'dialog form': [form] })[selector],
        getElementById: () => dialog,
    },
    window: { addEventListener: (event, handler) => { pageEvents[event] = handler; } },
});
check('Open and cancel a confirmation dialog', () => { openButton.click(); assert.equal(dialog.open, true); closeButton.click(); assert.equal(dialog.open, false); });
check('Page restore keeps server-disabled confirmation buttons disabled', () => { pageEvents.pageshow(); assert.equal(disabledByServer.disabled, true); });
function submitEvent() {
    return { defaultPrevented: false, preventDefault() { this.defaultPrevented = true; } };
}
check('First confirmation submits and disables the button', () => { const event = submitEvent(); formEvents.submit(event); assert.equal(event.defaultPrevented, false); assert.equal(submit.disabled, true); });
check('Repeated confirmation is prevented', () => { const event = submitEvent(); formEvents.submit(event); assert.equal(event.defaultPrevented, true); });
check('Returning to the page allows retry without enabling invalid actions', () => { pageEvents.pageshow(); assert.equal(submit.disabled, false); assert.equal(disabledByServer.disabled, true); const event = submitEvent(); formEvents.submit(event); assert.equal(event.defaultPrevented, false); });

const filterEvents = {};
const building = { value: '', addEventListener: (_, handler) => { filterEvents.building = handler; } };
const floor = { value: '', addEventListener: (_, handler) => { filterEvents.floor = handler; } };
const empty = { hidden: true };
function makeCard(buildingId, floorNumber) {
    const radio = { checked: true };
    return { hidden: false, dataset: { building: buildingId, floor: floorNumber }, querySelector: () => radio, radio };
}
const cards = [makeCard('T01', '1'), makeCard('T02', '2')];
runAsset('registration.js', {
    document: {
        getElementById: id => ({ 'building-filter': building, 'floor-filter': floor, 'room-empty': empty })[id],
        querySelectorAll: () => cards,
    },
});
check('Building filter hides other rooms and clears hidden selection', () => { building.value = 'T01'; filterEvents.building(); assert.equal(cards[0].hidden, false); assert.equal(cards[1].hidden, true); assert.equal(cards[1].radio.checked, false); assert.equal(empty.hidden, true); });
check('Combined filters show the empty state', () => { floor.value = '2'; filterEvents.floor(); assert.equal(cards.every(card => card.hidden), true); assert.equal(empty.hidden, false); });
check('Clearing filters shows rooms without restoring a hidden selection', () => { building.value = ''; floor.value = ''; filterEvents.building(); assert.equal(cards.every(card => !card.hidden), true); assert.equal(empty.hidden, true); assert.equal(cards[1].radio.checked, false); });
console.log(`${passed}/${passed} frontend checks passed.`);

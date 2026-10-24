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
const paymentEvents = {};
const paymentPageEvents = {};
const paymentButton = { disabled: false };
const paymentForm = { dataset: {}, hasAttribute: () => false, querySelector: () => paymentButton, addEventListener: (event, handler) => { paymentEvents[event] = handler; } };
const qrElement = { dataset: { paymentQr: '/Kanto-KTX/index.php?action=student-payment&invoice=TEST17' }, replaceChildren(image) { this.image = image; } };
const networkQr = { dataset: { paymentQr: 'http://192.0.2.10/Kanto-KTX/index.php?action=payment-checkout#' + 'a'.repeat(64), qrOrigin: 'http://192.0.2.10' }, replaceChildren(image) { this.image = image; } };
const foreignQr = { dataset: { paymentQr: 'https://other.example/payment' } };
let restored = false;
runAsset('student-payments.js', {
    URL,
    qrcode: require('../assets/js/vendor/qrcode.js'),
    document: {
        querySelectorAll: selector => selector === '[data-payment-qr]' ? [qrElement, networkQr, foreignQr] : [paymentForm],
        createElement: () => ({}),
    },
    window: {
        location: { href: 'http://localhost/Kanto-KTX/index.php', origin: 'http://localhost', reload: () => { restored = true; } },
        addEventListener: (event, handler) => { paymentPageEvents[event] = handler; },
    },
});
check('UC17 generates a local QR image with the bundled library', () => {
    assert(qrElement.image.src.startsWith('data:image/gif;base64,'));
    assert.equal(Buffer.from(qrElement.image.src.split(',')[1], 'base64').subarray(0, 3).toString(), 'GIF');
});
check('UC17 permits the server-configured LAN origin in a QR', () => { assert(networkQr.image.src.startsWith('data:image/gif;base64,')); });
check('UC17 refuses a QR pointing outside the application origin', () => { assert(foreignQr.textContent.includes('Chưa thể hiển thị QR')); });
check('UC17 permits one submit and blocks a double click', () => {
    let prevented = 0;
    paymentEvents.submit({ preventDefault: () => { prevented++; } });
    assert.equal(prevented, 0); assert.equal(paymentButton.disabled, true);
    paymentEvents.submit({ preventDefault: () => { prevented++; } });
    assert.equal(prevented, 1);
});
check('UC17 reloads restored pages to refresh paid status and transaction token', () => {
    paymentPageEvents.pageshow({ persisted: false }); assert.equal(restored, false);
    paymentPageEvents.pageshow({ persisted: true }); assert.equal(restored, true);
});
check('UC17 countdown expires without submitting a new QR request', () => {
    let elapsed = 0, timer, reloads = 0, submits = 0;
    const label = { textContent: '' };
    const countdown = { dataset: { qrExpires: '500', serverNow: '200' }, querySelector: () => label };
    const createForm = { dataset: {}, hasAttribute: name => name === 'data-qr-create', addEventListener: () => {}, submit: () => { submits++; } };
    runAsset('student-payments.js', {
        URL,
        performance: { now: () => elapsed },
        setInterval: handler => { timer = handler; },
        document: { querySelectorAll: selector => ({ '[data-payment-qr]': [], '[data-payment-confirm], [data-qr-create]': [createForm], '[data-qr-expires]': [countdown] })[selector] || [] },
        window: { location: { reload: () => { reloads++; } }, addEventListener: () => {} },
    });
    assert.equal(label.textContent, '5:00');
    elapsed = 300000; timer();
    assert.equal(label.textContent, '0:00'); assert.equal(reloads, 1); assert.equal(submits, 0);
});
function checkoutContext(hash) {
    const tokenInput = { value: '' };
    const state = { submits: 0, cleaned: '', message: {}, events: {}, button: { disabled: false } };
    const openForm = { querySelector: () => tokenInput, submit: () => { state.submits++; } };
    const confirmForm = { dataset: {}, querySelector: () => state.button, addEventListener: (event, handler) => { state.events[event] = handler; } };
    runAsset('payment-checkout.js', {
        document: {
            querySelector: selector => selector === '[data-checkout-open]' ? openForm : confirmForm,
            getElementById: () => state.message,
        },
        window: {
            location: { hash, pathname: '/Kanto-KTX/index.php', search: '?action=payment-checkout' },
            history: { replaceState: (_, __, url) => { state.cleaned = url; } },
            addEventListener: () => {},
        },
    });
    return { state, tokenInput };
}
check('QR checkout sends the fragment token via POST and removes it from history', () => {
    const {state,tokenInput} = checkoutContext('#' + 'a'.repeat(64));
    assert.equal(state.submits,1); assert.equal(tokenInput.value,'a'.repeat(64));
    assert.equal(state.cleaned,'/Kanto-KTX/index.php?action=payment-checkout');
});
check('QR checkout rejects a missing or malformed fragment without submitting', () => {
    for (const hash of ['', '#invalid', '#'+ 'a'.repeat(65)]) {
        const {state} = checkoutContext(hash);
        assert.equal(state.submits,0); assert(state.message.textContent.includes('không hợp lệ'));
    }
});
check('Anonymous QR checkout blocks double submission', () => {
    const {state} = checkoutContext('');
    let blocked = 0;
    state.events.submit({ preventDefault: () => { blocked++; } });
    assert.equal(blocked,0); assert.equal(state.button.disabled,true);
    state.events.submit({ preventDefault: () => { blocked++; } });
    assert.equal(blocked,1);
});
console.log(`${passed}/${passed} frontend checks passed.`);

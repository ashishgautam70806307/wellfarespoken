# Phase 144 Browser Test Checklist

## Preparation

1. Replace the project with the Phase 144 cumulative ZIP.
2. Clear the old Service Worker and site storage.
3. Reload with Ctrl + F5.
4. Use Chrome or Edge for microphone testing.
5. Allow microphone permission when requested.

## Spoken Materials — Voice Coach

### Speak Daily

- Select **Speak Daily**.
- Confirm only one loading state appears.
- Confirm the question is spoken once.
- Confirm the microphone opens after playback.
- Speak the same English sentence.
- Confirm the transcript appears in the answer field.
- Confirm the answer is checked once.
- Confirm correct feedback is visible and spoken.

### Repeat command

- Start another question.
- Say **again**, **repeat**, or **dobara bolo**.
- Confirm the answer field is cleared.
- Confirm only the current question is repeated.
- Confirm the mic opens once again.
- Confirm the page does not blink or continuously restart.

### Hindi to English

- Confirm the Hindi question uses Hindi voice output when available.
- Confirm the microphone expects an English answer.
- Confirm captured English is checked correctly.

### English to Hindi

- Confirm the English question is spoken.
- Confirm the microphone expects Hindi.
- Confirm Hindi speech is written into the answer field.

### Wrong answer

- Speak or type an incorrect answer.
- Confirm feedback stays in normal layout space.
- Confirm **Listen Correct Answer** works.
- Confirm Previous/Next controls remain usable.

### Manual controls

- Turn Voice Coach off.
- Confirm automatic question playback and mic opening stop.
- Confirm **Listen** works independently.
- Confirm **Speak answer** works independently.
- Confirm **Stop** ends the active mic session.
- Reload and confirm the selected Voice Coach preference is remembered.

## Stability

- Change mode while audio is playing.
- Move Previous/Next while mic is active.
- Hide/switch the browser tab while mic is active.
- Deny microphone permission and confirm typing remains available.
- Test a browser without SpeechRecognition and confirm a clear fallback message.
- Confirm Network shows one list request per mode selection and one answer request per check.

## Devices

- Desktop Chrome
- Desktop Edge
- Android Chrome
- 320px, 360px, 390px and 430px responsive widths

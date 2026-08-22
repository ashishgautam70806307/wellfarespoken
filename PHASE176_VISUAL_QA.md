# Phase 176 Visual QA

## Reference
Approved latest three-screen mobile mockup + user-supplied 320x455 screenshots of `spoken-materials.php`.

## Browser viewport checks

### 320 x 455
- Mobile app bar visible; old global header hidden.
- Spoken browse search/filter/cards remain readable.
- Active Spoken practice controls remain reachable by scrolling.
- Floating headset/contact dock does not overlap Previous/Next or Check controls.
- Bottom navigation remains attached and does not cover content.
- Practice page uses actual `.practice-lesson-panel` selector and renders compactly.
- Roadmap timeline and Continue action remain reachable.

### 390 x 844
- Spoken active practice controls fit cleanly in normal phone height.
- Practice title/search/shortcuts/stats/lesson/question hierarchy is clear.
- Roadmap progress/timeline hierarchy matches the approved mobile visual direction.

## Interaction checks
- Quick Spoken filters are real controls.
- Practice lesson search filters the real lesson selector.
- Drawer/menu button uses the existing drawer handler.
- Bottom-nav links are real application links.
- Mobile scroll-to-action behavior is immediate rather than delayed smooth scrolling.

## Remaining live-device checks
- Authenticated Android/iPhone visual pass with real database content.
- Real microphone permission / SpeechRecognition behavior on device.
- iOS Safari safe-area behavior on a physical device.

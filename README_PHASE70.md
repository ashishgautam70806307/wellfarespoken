# Phase 70 - Question number voice and 3-repeat flow

Changes:
- System now speaks question number + question text.
  Example: "Question number 4. You are busy."
- Each question is spoken 3 times before mic starts.
- After 3 repeats, system says "Now speak", then mic starts.
- Wrong answer retry repeats the same question 3 times again.
- Auto retry stops after 3 wrong attempts so it does not loop forever.
- Correct answer still shows for 2 seconds and auto-moves to next.

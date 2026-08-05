# Phase 69 - Now Speak prompt + stricter sentence checking

Changes:
- Every time mic starts, system first says: "Now speak".
- Mic starts after the "Now speak" voice ends, so it does not capture its own prompt.
- Strict English structure checking added:
  - "You are busy" is not accepted as "Are you busy".
  - Statement/question order must match.
  - Short sentences must match word order strongly.
- Correct answer auto next remains.
- Wrong answer says: "Not correct. Speak with me..." then repeats the same question and restarts mic.

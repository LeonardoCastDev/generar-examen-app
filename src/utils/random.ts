export function shuffle<T>(arr: T[], seed?: number): T[] {
  // Fisher–Yates with optional seed (LCG)
  const out = [...arr];
  let random = Math.random;
  if (typeof seed === "number") {
    let s = seed % 2147483647;
    if (s <= 0) s += 2147483646;
    random = () => (s = (s * 16807) % 2147483647) / 2147483647;
  }
  for (let i = out.length - 1; i > 0; i--) {
    const j = Math.floor(random() * (i + 1));
    [out[i], out[j]] = [out[j], out[i]];
  }
  return out;
}

export function createMarkup(markup: string): { __html: string } {
  // ! Add sanitize library before using markup
  return { __html: markup };
}

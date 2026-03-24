declare module 'html2pdf.js' {
  function html2pdf(): {
    from: (element: HTMLElement) => any;
    set: (options: any) => any;
    save: () => Promise<void>;
  };
  export default html2pdf;
}
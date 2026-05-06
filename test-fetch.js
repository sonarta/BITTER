fetch("http://localhost:8081/storage/resources/lkm-mahasiswa-20260503-121500.pdf", {
  cache: 'reload',
  credentials: 'same-origin',
  headers: { 'Cache-Control': 'no-cache', Pragma: 'no-cache' }
}).then(res => {
  console.log(res.status);
}).catch(err => console.error(err));

export function initDragDrop() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file-input');
    const fileName = document.getElementById('file-name');
    const fileError = document.getElementById('file-error');
    const form = document.getElementById('upload-form');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    if (!dropzone || !fileInput) return;

    function highlight(on) {
        dropzone.classList.toggle('ring-2', on);
        dropzone.classList.toggle('ring-indigo-300', on);
    }

    ['dragenter','dragover'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); highlight(true); }));
    ['dragleave','drop'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); highlight(false); }));

    dropzone.addEventListener('drop', e => {
        const dt = e.dataTransfer;
        if (dt && dt.files.length) {
            fileInput.files = dt.files;
            if (fileName) fileName.textContent = dt.files[0].name;
            validateSelectedFile(dt.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files.length && fileName) {
            fileName.textContent = fileInput.files[0].name;
            validateSelectedFile(fileInput.files[0]);
        }
    });

    if (form) {
        form.addEventListener('submit', e => {
            if (form.dataset.csvValid !== 'true') e.preventDefault();
        });
    }

    function setValidity(ok, message, foundHeaders) {
        if (!fileError) return;
        if (ok) {
            fileError.textContent = '';
            dropzone.classList.remove('ring-2', 'ring-red-300');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('opacity-50','cursor-not-allowed'); }
            if (form) { form.dataset.csvValid='true'; if(form.dataset.autoSubmitted!=='true'){form.dataset.autoSubmitted='true'; form.submit();} }
        } else {
            const details = foundHeaders?.length ? ` Found headers: ${foundHeaders.join(', ')}` : '';
            fileError.textContent = `${message}${details}`;
            dropzone.classList.add('ring-2','ring-red-300');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('opacity-50','cursor-not-allowed'); }
            if (form) { form.dataset.csvValid='false'; form.dataset.autoSubmitted='false'; }
        }
    }

    function validateSelectedFile(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = () => {
            const text = reader.result?.toString()||'';
            const firstLine = getFirstNonEmptyLine(text);
            if (!firstLine) { setValidity(false,'CSV appears to be empty. Expected headers: Name, Email, City, Phone'); return; }
            const headers = parseCsvHeaders(firstLine);
            const normalized = headers.map(h => h.replace(/^\ufeff/,'').trim().replace(/^"|"$/g,'').toLowerCase());
            const expected = ['name', 'email', 'city','phone'];
            const extra = normalized.filter(h => !expected.includes(h));
            const missing = expected.filter(h => !normalized.includes(h));
            if(normalized.length!==4 || extra.length>0 || missing.length>0){
                setValidity(false,'CSV headers must be exactly: Name, Email, City, Phone.', headers);
            } else setValidity(true);
        };
        reader.onerror = ()=>{ setValidity(false,'Could not read the file. Please try again.'); }
        reader.readAsText(file.slice(0,65536));
    }

    function getFirstNonEmptyLine(text){
        return text.split(/\r?\n/).find(line=>line.trim())||'';
    }

    function parseCsvHeaders(line){
        const result = []; let current=''; let inQuotes=false;
        for(let i=0;i<line.length;i++){
            const ch=line[i];
            if(ch=='"'){
                if(inQuotes && line[i+1]=='"'){ current+='"'; i++; } else inQuotes=!inQuotes;
            } else if(ch==',' && !inQuotes){ result.push(current.trim()); current=''; }
            else current+=ch;
        }
        result.push(current.trim());
        return result;
    }
}

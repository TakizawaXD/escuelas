import os

replacements = {
    # 1. Update Labels
    'class="block text-sm font-bold text-slate-700 mb-2"': 'class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2"',
    'class="block text-sm font-semibold text-slate-700 mb-1.5"': 'class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2"',

    # 2. Update text Inputs
    'class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-800 transition"': 'class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"',
    
    'class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-800 transition"': 'class="w-full pl-11 pr-4 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"',

    # 3. Form containers
    'class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 max-w-2xl"': 'class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 max-w-3xl mx-auto relative overflow-hidden group"',
    
    'class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 max-w-3xl mx-auto"': 'class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 max-w-3xl mx-auto relative overflow-hidden group"',

    # 4. Buttons
    'class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]"': 'class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center"',

    'class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]"': 'class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-4 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all"',
}

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    original = content
    for old, new in replacements.items():
        content = content.replace(old, new)
        
    # Inject ambient background into the new container if replaced
    if "relative overflow-hidden group" in content and "<!-- ambient background -->" not in content:
        target = 'max-w-3xl mx-auto relative overflow-hidden group">'
        injection = target + '\n    <!-- ambient background -->\n    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>\n    <div class="relative z-10">'
        content = content.replace(target, injection)
        
        # fix closing tag
        if '<div class="relative z-10">' in content:
            content = content.replace("</div>\n\n<?php\ninclude __DIR__ . '/../../views/layout/footer.php';", "</div>\n</div>\n\n<?php\ninclude __DIR__ . '/../../views/layout/footer.php';")
            content = content.replace("</div>\n<script>", "</div>\n</div>\n<script>")

    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated: {filepath}")

for root, dirs, files in os.walk('/home/andres/Escuelas/escuelas/modules'):
    for file in files:
        if file in ['create.php', 'edit.php']:
            process_file(os.path.join(root, file))


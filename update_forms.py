import os
import re
import glob

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    original = content

    # 1. Update form container
    content = re.sub(
        r'<div class="max-w-[a-z0-9-]+ mx-auto bg-white rounded-3xl shadow-sm border border-slate-100 p-8">',
        r'<div class="max-w-3xl mx-auto bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-8 md:p-12 relative overflow-hidden group">\n    <!-- Premium ambient background -->\n    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>\n    <div class="relative z-10">',
        content
    )
    
    # 2. Update Headers inside forms
    content = re.sub(
        r'<h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">',
        r'<h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">',
        content
    )

    # 3. Update Labels
    content = re.sub(
        r'class="block text-sm font-semibold text-slate-700 mb-1\.5"',
        r'class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2"',
        content
    )

    # 4. Update Inputs (text, email, password, number, select)
    content = re.sub(
        r'class="block w-full px-4 py-2\.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"',
        r'class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"',
        content
    )
    
    content = re.sub(
        r'class="block w-full px-4 py-2\.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"',
        r'class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"',
        content
    )
    
    # 5. Update primary buttons
    content = re.sub(
        r'class="px-5 py-2\.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md"',
        r'class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2"',
        content
    )
    
    content = re.sub(
        r'class="px-5 py-2\.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md"',
        r'class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2"',
        content
    )
    
    content = re.sub(
        r'class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md px-5 py-2\.5"',
        r'class="w-full px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2"',
        content
    )

    # 6. Update secondary buttons (cancel)
    content = re.sub(
        r'class="px-5 py-2\.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-sm"',
        r'class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all text-sm"',
        content
    )

    # Fix the missing </div> tag for the relative z-10 wrap if it was added
    if "relative z-10" in content and "<!-- end form wrapper -->" not in content:
        # try to find the end of the form or the closing tag for the card
        # The card closing tag is usually right before <?php include footer
        content = content.replace("</div>\n\n<?php\ninclude __DIR__ . '/../../views/layout/footer.php';", "</div>\n</div>\n\n<?php\ninclude __DIR__ . '/../../views/layout/footer.php';")
        content = content.replace("</div>\n<script>", "</div>\n</div>\n<script>")

    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated: {filepath}")

for root, dirs, files in os.walk('/home/andres/Escuelas/escuelas/modules'):
    for file in files:
        if file in ['create.php', 'edit.php', 'public_form.php']:
            process_file(os.path.join(root, file))


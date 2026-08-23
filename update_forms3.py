import os
import re

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    original = content

    # 1. Update text Inputs (textarea)
    content = re.sub(
        r'class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm"',
        r'class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"',
        content
    )
    
    # 2. Update Label
    content = re.sub(
        r'class="block text-sm font-bold text-slate-700"',
        r'class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2"',
        content
    )

    # 3. Form containers
    content = re.sub(
        r'class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100(.*?)"',
        r'class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group\1"',
        content
    )

    # 4. Buttons
    content = re.sub(
        r'class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-\[0.98\]"',
        r'class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2"',
        content
    )

    if content != original:
        # Check if we need to add background blob inside form or container
        if 'class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group' in content and "ambient background" not in content:
            target_str = 'group">'
            if target_str in content:
                 content = content.replace(target_str, target_str + '\n    <!-- ambient background -->\n    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>\n    <div class="relative z-10">', 1)
                 
                 # if form, we need to add </div> before </form>
                 if '</form>' in content:
                     content = content.replace('</form>', '</div>\n</form>')
                 elif '</div>\n\n<?php' in content:
                     content = content.replace("</div>\n\n<?php", "</div>\n</div>\n\n<?php")

        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated: {filepath}")

for root, dirs, files in os.walk('/home/andres/Escuelas/escuelas/modules'):
    for file in files:
        if file in ['create.php', 'edit.php']:
            process_file(os.path.join(root, file))


<?php
namespace App\Http\Controllers;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
class ExhibitorBadgeController extends Controller
{
    private function profile(Request $request) { return $request->user()->exhibitorProfile()->firstOrFail(); }
    public function index(Request $request): View { $exhibitor=$this->profile($request); return view('exhibitor.badges.index',['user'=>$request->user(),'exhibitor'=>$exhibitor,'badges'=>$exhibitor->badges()->latest()->paginate(10),'count'=>$exhibitor->badges()->count()]); }
    public function create(Request $request): View { $e=$this->profile($request); abort_if($e->badges()->count()>=$e->authorized_badges,422); return view('exhibitor.badges.form',['user'=>$request->user(),'badge'=>new Badge,'action'=>route('exhibitor.badges.store'),'method'=>null]); }
    public function store(Request $request): RedirectResponse { $e=$this->profile($request); $data=$request->validate(['first_name'=>['required','string','max:255'],'last_name'=>['required','string','max:255'],'function'=>['required','string','max:255'],'email'=>['required','email','max:255'],'phone'=>['nullable','string','max:40']]); DB::transaction(function()use($e,$data){$locked=$e->newQuery()->lockForUpdate()->findOrFail($e->id); if ($locked->badges()->count() >= $locked->authorized_badges) abort(422,'Quota dyal badges salat.'); $locked->badges()->create($data);}); return redirect()->route('exhibitor.badges.index')->with('status','Badge ajouté.'); }
    private function owns(Request $request, Badge $badge): void { abort_unless($request->user()->exhibitorProfile()->whereKey($badge->exhibitor_id)->exists(),403); }
    public function edit(Request $request, Badge $badge): View { $this->owns($request,$badge); return view('exhibitor.badges.form',['user'=>$request->user(),'badge'=>$badge,'action'=>route('exhibitor.badges.update',$badge),'method'=>'put']); }
    public function update(Request $request, Badge $badge): RedirectResponse { $this->owns($request,$badge); $badge->update($request->validate(['first_name'=>['required','string','max:255'],'last_name'=>['required','string','max:255'],'function'=>['required','string','max:255'],'email'=>['required','email','max:255'],'phone'=>['nullable','string','max:40']])); return back()->with('status','Badge modifié.'); }
    public function destroy(Request $request, Badge $badge): RedirectResponse { $this->owns($request,$badge); $badge->delete(); return back()->with('status','Badge supprimé.'); }
}

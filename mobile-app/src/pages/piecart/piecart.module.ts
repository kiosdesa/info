import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { PiecartPage } from './piecart';

@NgModule({
  declarations: [
    PiecartPage,
  ],
  imports: [
    IonicPageModule.forChild(PiecartPage),
    TranslateModule.forChild()
  ],
})
export class PiecartPageModule {}

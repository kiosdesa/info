import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { FavoritesPage } from './favorites';

@NgModule({
  declarations: [
    FavoritesPage,
  ],
  imports: [
    IonicPageModule.forChild(FavoritesPage),
    TranslateModule.forChild()
  ],
})
export class FavoritesPageModule {}
